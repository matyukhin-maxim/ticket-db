<?php

/**
 * Created by PhpStorm.
 * User: Матюхин_МП
 * Date: 22.12.2015
 * Time: 15:11
 */

/** @todo Убрать из списка отделов для согласования собственный цех, чтобы не отправлять заявки самому себе */

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
		if (get_param($this->authdata, 'role_id') !== Configuration::$ROLE_USER) {
			// Создать новую заявку может только Руководитель
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
		$my_dep = get_param($this->authdata, 'depid');
		$my_role = get_param($this->authdata, 'role_id');

		if (!$ticket) {
			$this->prepareError('Заявка не найдена', 'alert-warning');
			$this->redirect('/contents/');
		} elseif (get_param($ticket, 'status') == STATUS_DRAFT) {
			if (get_param($ticket, 'department_id') != $my_dep) {
				$this->prepareError('Редактировать черновики чужого цеха запрещено');
				$this->redirect('/contents/');
			}
		}

		$this->data['title'] = 'Редактирование заявки ';
		$this->data['t_number'] = get_param($ticket, 'number');
		$this->data['t_cdate'] = sqldate2human(get_param($ticket, 'dt_create'), 'd.m.Y');
		$this->data['t_message'] = get_param($ticket, 'message');
		$this->data['t_id'] = $req_id;

		// кнопка удаления заявки доступна только из черновика
		$this->data['button_delete'] = sprintf('<a class="btn btn-danger" href="/ticket/delete/%s/">Удалить</a>', $req_id);

		$departments = $this->model->getDepartments();
		$tdepid = get_param($ticket, 'department_id');
		$dlist = array_column($departments, 'title', 'id');

		// цех создатель
		$this->data['t_department'] = get_param($dlist, $tdepid, '?');

		// цех согласователь
		$agree = get_param($ticket, 'agree');
		$adepid = get_param($agree, 'department_id', -1);
		$this->data['departments'] = generateOptions($departments, $adepid, 'Не требуется');
		$adepname = get_param($dlist, $adepid, null); // Название цеха, кто должен согласовать

		// пользователь создавший заявку
		$uid = get_param($ticket, 'user_id');
		$user = $this->model->getUserName($uid);
		$this->data['t_user'] = get_param($user, 'fullname', '?');


		$nodes = $this->model->getNodes();
		$nodeid = get_param($ticket, 'node_id');
		$nlist = array_column($nodes, 'title', 'id');
		$this->data['nodename'] = get_param($nlist, $nodeid, '?');

		// получим список устройств узла, и нарисуем чекбокс-лист
		// но изменим порядок, чтобы спервы отрисовались отмеченные в заявке устройства
		$dlist = $this->model->getDevices($nodeid);
		$dlist = array_column($dlist, 'name', 'id');
		$devs = get_param($ticket, 'devices', []);

		$this->data['tstart'] = sqldate2human(get_param($ticket, 'dt_start'));
		$this->data['tstop'] = sqldate2human(get_param($ticket, 'dt_stop'));
		$this->data['resolutions'] = '';
		$ticket_status = intval(get_param($ticket, 'status', -1));

		$devices = '';
		// обходим перечень устройств указанных в заявке
		foreach ($devs as $item_id) {
			// получаем название из списка
			$devname = get_param($dlist, $item_id);

			$this->data['mark'] = 1;
			$this->data['dev_name'] = $devname;
			$this->data['dev_id'] = $item_id;
			$this->data['dev_class'] = mb_strlen($devname) >= 35 ? 'col-md-12' : 'col-md-6';
			$devices .= $ticket_status === STATUS_DRAFT ? $this->renderPartial('device-item') : $devname . PHP_EOL;
		}

		if ($ticket_status !== STATUS_DRAFT) $devices = nl2br($devices);
		if (!count($dlist)) {
			$devices = $this->renderPartial('no-device');
		} elseif (!(count($devs) || $ticket_status === STATUS_DRAFT)) {
			$devices = "Устройства не указаны";
		}

		$this->scripts[] = 'debug-reload';

		switch ($ticket_status) {
			case STATUS_DRAFT : {
				$template = 'new-ticket';
				$this->data['nodes'] = generateOptions($nodes, $nodeid, false);

				// дорисуем все остальные устройства выбранного узла, тчобы их можно было при необходимости выбрать
				foreach ($dlist as $key => $value) {

					if (!in_array($key, $devs)) {

						$this->data['mark'] = 0;
						$this->data['dev_name'] = $value;
						$this->data['dev_id'] = $key;
						$this->data['dev_class'] = mb_strlen($value) >= 35 ? 'col-md-12' : 'col-md-6';
						$devices .= $this->renderPartial('device-item');
					}
				}
			}
				break;
			case STATUS_AGREE : {
				$template = 'ticket-panel-agree';
				$this->data['title'] = 'Согласование заявки';

				$ares = get_param($agree, 'result', null);
				// Проверка и защита от дурака
				if (!$adepid || $ares) {
					// Если id цеха, который должен согласовывать, не указан, или согласование уже получено (!= null)
					// то статус у заявки неверный, и его желательно исправить
					// (отправляем на согласование, представивщись Роботом
					$this->model->setTicketStatus($req_id, STATUS_REVIEW, 0);
					$this->prepareError('Ошибочный статус заявки');
					$this->redirect('/');
				} elseif ($my_dep === $adepid && $my_role === Configuration::$ROLE_USER) {
					// Если согласовать должен цех авторизованного пользователя, то нарисуем форму и кнопку
					// js скрипт перехватит нажатие кнопки сохранения и сделает всю грязную работу
					$this->data['panel_title'] = "Согласование цеха $adepname";
					$this->data['panel_content'] = $this->renderPartial('form-agree');
					$this->data['resolutions'] .= $this->renderPartial('resolution-panel');

					$this->scripts[] = 'agreement';
				} else {
					$this->data['panel_title'] = "Согласование цеха $adepname";
					$this->data['panel_content'] = "<em>Ожидание решения руководителя цеха</em>";
					$this->data['resolutions'] .= $this->renderPartial('resolution-panel');
				}

			}
				break;
			case STATUS_REVIEW : {
				$template = 'ticket-panel-review';
				$this->data['title'] = 'Рассмотрение заявки';

				if ($agree) {
					// заполним информацию о том кто и когда согласовал
					$this->data['ag_res'] = 'Согласованно';
					$this->data['ag_class'] = 'alert-success';
					$this->data['ag_user'] = makeSortName(get_param($agree, 'fullname', '-'));
					$this->data['ag_date'] = sqldate2human(get_param($agree, 'dt_stamp'));

					$this->data['panel_title'] = "Резолюция цеха $adepname";
					$this->data['panel_content'] = $this->renderPartial('agree-info');
					$this->data['resolutions'] .= $this->renderPartial('resolution-panel');
				} else {
					$this->data['panel_title'] = "Согласование цеха";
					$this->data['panel_content'] = "<em>Не требуется</em>";
					$this->data['resolutions'] .= $this->renderPartial('resolution-panel');
				}

				if ($my_role === Configuration::$ROLE_ME) {

					$this->data['panel_title'] = 'Резолюция главного инженера';
					$this->data['panel_content'] = $this->renderPartial('form-review');
					$this->data['resolutions'] .= $this->renderPartial('resolution-panel');

					$this->scripts[] = 'review-ticket';
				} else {
					$this->data['panel_title'] = 'Резолюция главного инженера';
					$this->data['panel_content'] = "<em> Ожидание... </em>";
					$this->data['resolutions'] .= $this->renderPartial('resolution-panel');
				}
			}
				break;
			case STATUS_ACCEPT : {
				$template = 'ticket-panel-accept';
				$this->data['title'] = 'Обработка заявки';

				if ($agree) {
					// заполним информацию о том кто и когда согласовал
					$this->data['ag_res'] = 'Согласованно';
					$this->data['ag_class'] = 'alert-success';
					$this->data['ag_user'] = makeSortName(get_param($agree, 'fullname', '-'));
					$this->data['ag_date'] = sqldate2human(get_param($agree, 'dt_stamp'));

					$this->data['panel_title'] = "Резолюция цеха $adepname";
					$this->data['panel_content'] = $this->renderPartial('agree-info');
					$this->data['resolutions'] .= $this->renderPartial('resolution-panel');
				} else {
					$this->data['panel_title'] = "Согласование цеха";
					$this->data['panel_content'] = "<em>Не требуется</em>";
					$this->data['resolutions'] .= $this->renderPartial('resolution-panel');
				}

			}
				break;
			default:
				$template = 'incorrect-status';
		}

		$this->data['devlist'] = $devices;
		$this->render($template);

	}

	public function actionDelete() {

		$ticket_id = filter_var(get_param($this->arguments, 0, -1), FILTER_VALIDATE_INT);

		$info = $this->model->getTicketInfo($ticket_id);
		if ($info['status'] !== '1') {
			$this->prepareError('Удалять можно только черновик.');
			$this->redirect('/contents/');
		} elseif ($info['department_id'] != get_param($this->authdata, 'depid')) {
			$this->prepareError('Заявку другого цеха удалять запрещено!');
			$this->redirect('/contents/');
		}

		$res = $this->model->deleteDraft($ticket_id);
		if ($res) {
			$this->prepareError('Заявка удалена.', 'alert-success');
			$this->model->setTicketStatus($ticket_id, -1);
		} else {
			$this->prepareError('Заявка не найдена.');
		}

		$this->redirect('/contents/');
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
			't_message' => FILTER_SANITIZE_SPECIAL_CHARS,
			'devices' => [
				'filter' => FILTER_VALIDATE_INT,
				'flags' => FILTER_REQUIRE_ARRAY,
			],
			't_number' => FILTER_SANITIZE_STRING,
			't_cdate' => [
				'filter' => FILTER_VALIDATE_REGEXP,
				'options' => [
					'regexp' => '/^(\d{2}\.){2}\d{4}$/',
					'default' => date('d.m.Y'),
				],
			],
			'confirm' => [
				'filter' => FILTER_VALIDATE_INT,
				'options' => [
					'default' => 0,
				],
			],
		]);

		$info['devices'] = $info['devices'] ?: [];

		// дату создания добиваем нулями (время), и преобразум к mysql формату
		$info['t_cdate'] = date2mysql($info['t_cdate'] . ' 00:00');

		// обнуляем номер заявки если это новая (чтобы номер сгенерировался автоматически)
		if ($info['t_number'] === '-') $info['t_number'] = null;

		$user_id = get_param($this->authdata, 'id', null);
		// информацию о создателе берем из сессии пользователя
		$info['user'] = $user_id;
		$info['depid'] = get_param($this->authdata, 'depid', null);

		//var_dump($info);

		$res = $this->model->saveTicket($info);
		if ($res) {
			$this->prepareError('Заявка сохранена.', 'alert-success');
			$this->model->setTicketStatus($res, STATUS_DRAFT, $user_id);

			// если отправляем на согасование, то установим соответствующий статус
			if ($info['confirm'] === 1) {
				// Если agreement задан, то заявка уходит в согласование цеха, иначе сразу к ГИ
				$this->model->setTicketStatus($res, $info['agreement'] ? STATUS_AGREE : STATUS_REVIEW, $user_id);
				$this->prepareError('Заявка отправлена на рассмотрение', 'alert-info');
			}

		} else $this->prepareError('Ошибка создания заявки.');

		//echo json_encode($res ? $this->createActionUrl("edit/$res") : '/');
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

	public function ajaxAgreement() {

		// проверим чтоб статус заявки был нужный
		// и что у пользоателя есть право согласования
		$role = get_param($this->authdata, 'role_id');
		$depid = get_param($this->authdata, 'depid');
		$me = get_param($this->authdata, 'id');
		$request = filter_input_array(INPUT_POST, [
			'ticket_id' => [
				'filter' => FILTER_VALIDATE_INT,
				'options' => [
					'default' => -1,
					'min_range' => 1
				],
			],
			'result' => [
				'filter' => FILTER_VALIDATE_INT,
				'options' => [
					'default' => null,
					'min_range' => 0,
					'max_range' => 1,
				],
			],
			'agree-text' => FILTER_SANITIZE_SPECIAL_CHARS,
		]);

		$tid = get_param($request, 'ticket_id');
		$info = $this->model->getTicketInfo($tid);
		$agree = get_param($info, 'agree'); // информация о цехе, кто должен согласовать
		$need = get_param($agree, 'department_id'); // его id

		if ($role !== Configuration::$ROLE_USER) {
			$this->prepareError('Согласовывать заявки могут только руководители цехов', 'alert-warning');
			return;
		} elseif (!$info) {
			$this->prepareError('Запрошеная заявка не найдена');
			return;
		} elseif (!$agree) {
			$this->prepareError('Заявка не нуждается в согласовании', 'alert-warning');
			return;
		} elseif ($depid !== $need) {
			$this->prepareError('Заявку должен согласовывать <br/> руководитель другого цеха ', 'alert-warning');
			return;
		}

		$result = get_param($request, 'result', null);

		$this->model->setAgreement($tid, $need, $me, $result, get_param($request, 'agree-text'));
		if ($result !== null) {
			// если получили согласование или отказ, то нужно установить новый статус заявки
			$this->model->setTicketStatus($tid, $result === 1 ? STATUS_REVIEW : STATUS_REJECT, $me);
		}

		if (count($this->model->getErrors())) {
			echo CModel::getErrorList();
		} else {
			$this->prepareError('Заявка сохранена', 'alert-success');
		}
	}

	public function ajaxConfirmation() {

		// проверим чтоб статус заявки был нужный
		// и что у пользоателя есть право согласования
		$role = get_param($this->authdata, 'role_id');
		$me = get_param($this->authdata, 'id');
		$request = filter_input_array(INPUT_POST, [
			'ticket_id' => [
				'filter' => FILTER_VALIDATE_INT,
				'options' => [
					'default' => -1,
					'min_range' => 1
				],
			],
			'result' => [
				'filter' => FILTER_VALIDATE_INT,
				'options' => [
					'default' => null,
					'min_range' => 1,
					'max_range' => 3,
				],
			],
			'agree-text' => FILTER_SANITIZE_SPECIAL_CHARS,
		]);

		$tid = get_param($request, 'ticket_id');
		$info = $this->model->getTicketInfo($tid);

		/** @todo delete line below */
		$role = Configuration::$ROLE_ME; // for debug

		if ($role !== Configuration::$ROLE_ME) {
			$this->prepareError('Согласовывать заявки может только главный инженер', 'alert-warning');
			return;
		} elseif (!$info) {
			$this->prepareError('Запрошеная заявка не найдена');
			return;
		} elseif (get_param($info, 'status') != STATUS_REVIEW) {
			$this->prepareError('Неправильный статус рассматриваемой заявки');
			return;
		}

		$result = get_param($request, 'result', null);

		if ($result !== null) {
			$this->model->setConfirmation($tid, $me, $result, get_param($request, 'agree-text'));

			// в зависимости от результата рассмотрения, установим новый статус заявки
			$this->model->setTicketStatus($tid, $result === 3 ? STATUS_REJECT : STATUS_ACCEPT, $me);
		}

		if (count($this->model->getErrors())) {
			echo CModel::getErrorList();
		} else {
			$this->prepareError('Заявка сохранена', 'alert-success');
		}
	}

}