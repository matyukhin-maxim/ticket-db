<?php

/**
 * Created by PhpStorm.
 * User: Матюхин_МП
 * Date: 22.12.2015
 * Time: 15:11
 */

/** @property TicketModel $model */
class TicketController extends CController {

	public function __construct() {
		parent::__construct();

		if (!$this->authdata) {
			$this->redirect('/auth/');
			return;
		}

		$this->scripts[] = 'ticket-validate';
	}

	public function actionIndex() {

		$this->actionCreate();
	}

	public function actionCreate() {

		// если какм-то чудом сюда попал не руководитель, то отправляем его на главную страницу
		if (get_param($this->authdata, 'role_id') < Configuration::$ROLE_USER) {
			// Создать новую заявку может только Руководитель
			// и в крайнем случае Админ (id роли Админа > id роли Руководителя)
			$this->render('block-create', false);
			$this->redirect([
				'location' => '/contents/',
			    'soft' => 1,
			    'delay' => 5,
			]);
			$this->render('');
			return;
		}

		$this->data['title'] = 'Новая заявка';
		$this->data['t_number'] = '-';
		$this->data['t_cdate'] = date('d.m.Y');
		$this->data['t_department'] = get_param($this->authdata, 'depname');
		$this->data['t_user'] = get_param($this->authdata, 'fullname');
		$this->data['t_message'] = '';
		$this->data['t_id'] = null;

		$departments = $this->model->getDepartments();
		$this->data['departments'] = generateOptions($departments, null, 'Не требуется');

		$nodes = $this->model->getNodes();
		$this->data['nodes'] = generateOptions($nodes, null, false);

		$this->render('new-ticket');
	}

	public function actionEdit() {

		// Получим номер запрашиваемой заявки, ее статус и цех-создатель
		// для проверки возможности редактирования
		$req_id = get_param($this->arguments, 0);
		$req_id = filter_var($req_id, FILTER_VALIDATE_INT, [
			'options' => [
				'min_range' => 1,
			    'default' => -1,
			],
		]);


		$this->render('', false);
		$ticket = $this->model->getTicketInfo($req_id);

		if (!$ticket) {
			$this->prepareError('Заявка не найдена', 'alert-warning');
			$this->redirect('/contents/');
		} elseif (get_param($ticket, 'status') == 1) {
			if (get_param($ticket, 'department_id') != get_param($this->authdata, 'depid')) {
				$this->prepareError('Редактировать черновики чужого цеха запрещено');
				$this->redirect('/contents/');
			}
		}

		$this->data['title'] = 'Редактирование заявки ';
		$this->data['t_number'] = get_param($ticket, 'number');
		$this->data['t_cdate'] = sqldate2human(get_param($ticket, 'dt_create'), 'd.m.Y');
		$this->data['t_message'] = get_param($ticket, 'message');
		$this->data['t_id'] = $req_id;

		$departments = $this->model->getDepartments();
		$tdep  = get_param($ticket, 'department_id');
		$dlist = array_column($departments, 'title', 'id');

		// цех создатель
		$this->data['t_department'] = get_param($dlist, $tdep, '?');

		// цех согласователь
		$agree = get_param($ticket, 'agree');
		$adep = get_param($agree, 'department_id', null);
		$this->data['departments'] = generateOptions($departments, $adep, 'Не требуется');

		// пользователь создавший заявку
		$uid = get_param($ticket, 'user_id');
		$user = $this->model->getUserName($uid);
		$this->data['t_user'] = get_param($user, 'fullname', '?');


		$nodes = $this->model->getNodes();
		$nodeid = get_param($ticket, 'node_id');
		$this->data['nodes'] = generateOptions($nodes, $nodeid, false);

		// получим список устройств узла, и нарисуем чекбокс-лист
		// но изменим порядок, чтобы спервы отрисовались отмеченные в заявке устройства
		$dlist = $this->model->getDevices($nodeid);
		$dlist = array_column($dlist, 'name', 'id');
		$devs = get_param($ticket, 'devices', []);
		foreach ($devs as $item_id) {
			$item = get_param($dlist, $item_id);
			if ($item) {
				unset($dlist[$item_id]);
				$dlist = [$item_id => $item] + $dlist; // ставим на первое место
			}
		}

		$devices = '';
		if (count($dlist) === 0) {
			$devices .= $this->renderPartial('no-device');
		} else {
			foreach ($dlist as $key => $value) {
				$this->data['mark'] = (int)in_array($key, $devs);
				$this->data['dev_name'] = $value;
				$this->data['dev_id'] = $key;
				$this->data['dev_class'] = mb_strlen($value) >= 35 ? 'col-md-12' : 'col-md-6';
				$devices .= $this->renderPartial('device-item');
			}
		}
		$this->data['devlist'] = $devices;

		$this->data['tstart'] = sqldate2human(get_param($ticket, 'dt_start'));
		$this->data['tstop' ] = sqldate2human(get_param($ticket, 'dt_stop'));


		$this->render('new-ticket');

		//var_dump($ticket);

		/** @todo Алгоритм следующий:
		 * Получаем статус заявки
		 * Если это черновик, то просматривать его может
		 * только пользователь цеха, который ее создал
		 * В этом случае получаем всю информацию по заявке, и выводим шаблон новой,
		 * передав текщие параметры
		 * @todo [добавить в шаблон кнопку удаления заявки]
		 *
		 * Если черновик но цех левый,
		 * (явно жульничество, т.к. отобразиться в списке у него она не могла),
		 * то просто шлем нах
		 *
		 * Иначе в зависисости от стутуса рисуем опредиленый шаблон
		 */
	}

	public function ajaxSave() {
		//var_dump($_POST);

		$info = filter_input_array(INPUT_POST, [
			'ticket_id' => [
				'filter' => FILTER_VALIDATE_INT,
				'options' => [
					'default' => null,
					'min_range' => 1
				],
			],
			't_node' => [
				'filter' => FILTER_VALIDATE_INT,
				'options' => [
					'default' => null,
					'min_range' => 1
				],
			],
			'agreement' => [
				'filter' => FILTER_VALIDATE_INT,
				'options' => [
					'default' => null,
					'min_range' => 1
				],
			],
			'td_start' => [
				'filter' => FILTER_VALIDATE_REGEXP,
				'options' => [
					'regexp' => '/^(\d{2}\.){2}\d{4}\s+(\d{2}\:?){2}$/',
					'default' => date('d.m.Y H:i'),
				],
			],
			'td_stop' => [
				'filter' => FILTER_VALIDATE_REGEXP,
				'options' => [
					'regexp' => '/^(\d{2}\.){2}\d{4}\s+(\d{2}\:?){2}$/',
					'default' => date('d.m.Y H:i'),
				],
			],
			't_message' => FILTER_SANITIZE_STRING,
			'devices' => [
				'filter' => FILTER_VALIDATE_INT,
				'flags' => FILTER_REQUIRE_ARRAY,
			],
		    't_number' => FILTER_SANITIZE_STRING,
		]);

		$info['devices'] = $info['devices'] ?: [];

		// обнуляем номер заявки если это новая (чтобы номер сгенерировался автоматически)
		if ($info['t_number'] === '-') $info['t_number'] = null;

		// информацию о создателе берем из сессии пользователя
		$info['user'] = get_param($this->authdata, 'id', null);
		$info['depid'] = get_param($this->authdata, 'depid', null);

		$res = $this->model->saveTicket($info);
		if ($res) {
			$this->prepareError('Заявка сохранена.', 'alert-success');
		} else $this->prepareError('Ошибка создания заявки.');

		echo json_encode($res ? $this->createActionUrl("edit/$res") : '/');
	}

	public function ajaxDevices() {

		$node = filter_input(INPUT_POST, 'node', FILTER_VALIDATE_INT);
		$devices = $this->model->getDevices($node);
		$data = '';

		if (count($devices) === 0) {
			$data .= $this->renderPartial('no-device');
		} else {
			foreach ($devices as $item) {
				$this->data['mark'] = 0;
				$this->data['dev_name'] = get_param($item, 'name');
				$this->data['dev_id'] = get_param($item, 'id');
				$this->data['dev_class'] = mb_strlen($this->data['dev_name']) >= 35 ? 'col-md-12' : 'col-md-6'; // fine view of wide name
				$data .= $this->renderPartial('device-item');
			}
		}

		echo $data;
	}
}