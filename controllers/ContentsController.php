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
		if (get_param($this->authdata, 'role_id') === Configuration::$ROLE_USER) {
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

		/*
		echo json_encode(array_map(function ($x) {
			return mt_rand(-10, 5);
		}, range(1, count($this->menu))));
		*/
		$udep = get_param($this->authdata, 'depid', 0);
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

		$ticketlist = $this->model->getTicketListByStatus($status, get_param($this->authdata, 'depid'));
		if (count($ticketlist) == 0) echo $this->renderPartial('no-ticket');
		foreach ($ticketlist as $ticket) {

			$this->data['tn'] = get_param($ticket, 'number');
			$this->data['dcreate'] = get_param($ticket, 'dc');
			$this->data['tdepartment'] = get_param($ticket, 'dname');
			$this->data['twork'] = join('<br/>', get_array_part($ticket, 'dstart dstop'));
			$this->data['tnode'] = get_param($ticket, 'nodename');
			$this->data['tstatus'] = 'Черновик';
			$this->data['tid'] = get_param($ticket, 'id');

			echo $this->renderPartial('ticket-row');
		}
	}
}