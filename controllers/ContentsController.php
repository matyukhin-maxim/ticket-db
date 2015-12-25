<?php

class ContentsController extends CController {

	private $menu = [
		'confirm'   => 'Согласование',
		'accept'    => 'Разрешенные',
		'open'      => 'Открытые',
		'complete'  => 'Прикрытые',
		'reject'    => 'Отказанные',
		'close'     => 'Закрытые',
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
			$this->data['cnt']      = rand(1, 15);
			$this->data['title']    = $title;
			$this->data['url']      = $this->createActionUrl($url);
			$this->data['usermenu'] .= $this->renderPartial('menu-item');
		}

		$this->scripts[] = 'contents';
	}

	public function actionIndex() {

		$this->render('list');
	}

	public function ajaxCount() {

		echo json_encode(array_map(function($x) {return mt_rand(-10, 5);}, range(1, count($this->menu))));
	}
}