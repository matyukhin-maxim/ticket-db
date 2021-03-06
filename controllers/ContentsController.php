<?php

/** @property ContentsModel $model */
class ContentsController extends CController {

	private $menu = [
		STATUS_AGREE => 'Согласование',
		STATUS_REVIEW => 'На рассмотрении',
		STATUS_ACCEPT => 'Разрешенные',
		STATUS_OPEN => 'Открытые',
		STATUS_COMPLETE => 'Прикрытые',
		STATUS_REJECT => 'Отказанные',
		STATUS_DELETE => 'Удаленные',
		STATUS_CLOSE => 'Закрытые',
	];

	public function __construct() {
		parent::__construct();

		// если в сессии нет информации об авторизации, отравляем на страницу с оной
		if (!$this->authdata) {
			$this->redirect('/auth/');
			return;
		}

		// если текущий пользователь - руководитель, то добавим ссылку на создание новой заявки
		//if (get_param($this->authdata, 'role_id') === Configuration::$ROLE_USER) {
		if ($this->isGrantToMe('ACE_NEW')) {
			$this->data['usermenu'] .= $this->renderPartial('new-create');

			// и пункт меню для черновиков
			$this->menu = [STATUS_DRAFT => 'Черновики',] + $this->menu;
		}

		// получим количество заявок в разрезе статусов
		$udep = get_param($this->authdata, 'depid', 0);
		$states = array_column($this->model->getCounter($udep), 'cnt', 'id');

		// рендерим основное меню
		foreach ($this->menu as $index => $title) {

			$this->data['cnt'] = $states[$index] ?: ''; //rand(1, 15);
			$this->data['title'] = $title;
			$this->data['type'] = $index; // статус заявок (для фильтрации)
			$this->data['usermenu'] .= $this->renderPartial('menu-item');
		}

		$this->scripts[] = 'contents';
	}

	public function actionIndex() {

		$this->render('list');
	}

	public function ajaxCount() {

		$udep = get_param($this->authdata, 'depid', -1);
		echo json_encode($this->model->getCounter($udep));
	}

	public function ajaxList() {

		/** @todo При запросе завок для согласования нужно сделать чтобы сверху были заявки цеха текущего пользователя и подсвечивались */

		//sleep(1);
		$status = filter_input(INPUT_POST, 'type', FILTER_VALIDATE_INT, [
			'options' => [
				'min_range' => 0,
				'default' => 0,
			]
		]);

		$dep_id = get_param($this->authdata, 'depid', -1);
		$role = get_param($this->authdata, 'role_id');
		$ticketlist = $this->model->getTicketListByStatus($status, $dep_id);
		if (count($ticketlist) == 0) echo $this->renderPartial('no-ticket');
		foreach ($ticketlist as $ticket) {

			/*$dnode = CHtml::createTag('div', null, [
				CHtml::createTag('span', ['class' => ''], get_param($ticket, 'nodename')),
				CHtml::createTag('br'),
				CHtml::createTag('em', ['class' => 'strong'], get_param($ticket, 'devs')),
			]);*/

			$dnode = CHtml::createTag('div', [
				'class' => '',
				'data-trigger' => 'hover',
				'data-content' => str_replace('|', "<br/>", htmlspecialchars(get_param($ticket, 'devs'))) ?: 'Не указаны',
				'data-original-title' => 'Механизмы',
				'data-toggle' => 'popover',
				'data-placement' => 'top',
			], get_param($ticket, 'nodename'));


			$tid = get_param($ticket, 'id');
			$this->data['tn'] = get_param($ticket, 'number');
			$this->data['dcreate'] = get_param($ticket, 'dc');
			$this->data['tdepartment'] = get_param($ticket, 'dname');
			// чтобы на разых экранах время не "упрыгивало" от даты на новую строку, разменим обычный пробел - неразрывным
			$this->data['twork'] = str_replace(' ', '&nbsp;', join('<br/>', get_array_part($ticket, 'dstart dstop')));
			$this->data['tnode'] = $dnode;
			$this->data['tid'] = $tid;

			$this->data['textra'] = CHtml::createLink('Просмотр', "/ticket/edit/$tid/", [
				'class' => 'btn btn-default btn-block',
			    'title' => 'Открыть заявку',
			]);

			// НСС просили подсвечивать заявки кторые близки к просрочке
			$open_class = '';
			$cday = get_param($ticket, 'cday', 10);
			if ($cday < 0) $open_class = 'danger';
			elseif ($cday <= 1) $open_class = 'warning strong';

			/* Подсветка строк списка заявок */
			$this->data['tclass'] = '';
			switch ($status) {
				case STATUS_AGREE:
					if ($role == Configuration::$ROLE_USER)
						$this->data['tclass'] = (get_param($ticket, 'adep') === $dep_id) ? 'warning strong' : '';
					break;
				case STATUS_OPEN:
					if ($role === Configuration::$ROLE_USER)
						$this->data['tclass'] = (get_param($ticket, 'depid') === $dep_id) ? 'warning strong' : '';
					if (in_array($role, [Configuration::$ROLE_NSS, Configuration::$ROLE_ADMIN]))
						$this->data['tclass'] = $open_class;
					break;
				default:
			}
			echo $this->renderPartial('ticket-row');
		}
	}
}