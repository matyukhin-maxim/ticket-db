<?php

/** @property ContentsModel $model */
class ContentsController extends CController {

	private $menu = [
		'confirm' => 'Согласование',
		'accept' => 'Разрешенные',
		'open' => 'Открытые',
		'complete' => 'Прикрытые',
		'reject' => 'Отказанные',
		'close' => 'Закрытые',
	];

	public function __construct() {
		parent::__construct();

		// если в сессии нет информации об авторизации, отравляем на страницу с оной
		if (!$this->authdata) {
			$this->redirect('/auth/');
			return;
		}

		// если текущий пользователь - руководитель, то добавим ссылку на создание новой заявки
		if (get_param($this->authdata, 'role_id') == Configuration::$ROLE_USER) {
			$this->data['usermenu'] .= $this->renderPartial('new-ticket');

			// и пункт меню для черновиков
			$this->menu = array_merge([
				'draft' => 'Черновики',
			], $this->menu);
		}

		// рендерим основное меню
		foreach ($this->menu as $url => $title) {
			$this->data['cnt'] = rand(1, 15);
			$this->data['title'] = $title;
			$this->data['url'] = $this->createActionUrl($url);
			$this->data['usermenu'] .= $this->renderPartial('menu-item');
		}

		$this->scripts[] = 'contents';
	}

	public function actionIndex() {


		$this->render('list');
	}

	public function ajaxCount() {

		echo json_encode(array_map(function ($x) {
			return mt_rand(-10, 5);
		}, range(1, count($this->menu))));
	}

	public function ajaxList() {

		sleep(3);
		$status = filter_input(INPUT_POST, 'type', FILTER_VALIDATE_INT, [
			'options' => [
				'min_range' => 0,
			    'default' => 0,
			]
		]);

		$ticketlist = $this->model->getTicketListByStatus($status);
		foreach ($ticketlist as $ticket) {

			$this->data['tn'] = get_param($ticket, 'number');
			$this->data['dcreate'] = get_param($ticket, 'dc');
			$this->data['tdepartment'] = get_param($ticket, 'dname');
			$this->data['twork'] = join('<br/>', get_array_part($ticket, 'dstart dstop'));
			$this->data['tnode'] = get_param($ticket, 'nodename');
			$this->data['tstatus'] = 'Черновик';

			echo $this->renderPartial('ticket-row');
		}
	}
}