<?php

/** @property AuthModel $model */
class AuthController extends CController {

	public function actionIndex() {

		//$needHelp = filter_input(INPUT_COOKIE, 'no-help', FILTER_VALIDATE_INT) ?: 0;
		//if ($needHelp === 0) $this->redirect('/about/');

		Session::del('auth');
		Session::destroy();
		//$this->scripts[] = 'auth';
		//$this->render('form');
		if (isPOST()) return $this->actionOpenID();

		$this->redirect('http://openid.asu.ngres/');
	}

	public function actionThx() {

		setcookie('no-help', 1, time() + 30 * 24 * 3600, '/');
		$this->redirect('/');
	}

	public function ajaxLogin() {

		$login = filter_input(INPUT_POST, 'tabel', FILTER_SANITIZE_STRING);
		$password = filter_input(INPUT_POST, 'password');

		$this->authdata = $this->model->setAuthenticate($login, $password);
		if (!$this->authdata) {
			$this->preparePopup("Ошибка авторизации.\nПользователь или пароль указаны неверно.");
		} elseif (!get_param($this->authdata, 'rolename')) {
			$this->preparePopup("Роль пользователя не определена.\nОбратитесь в отдел АСУ.", 'alert-warning');
		} else {
			Session::set('auth', $this->authdata);
			return;
		}

		echo 'error';
	}

	public function actionOpenID() {

		$login = filter_input(INPUT_POST, 'uid', FILTER_SANITIZE_STRING);

		$this->authdata = $this->model->openAuth($login);
		if (!$this->authdata) {
			$this->preparePopup("Ошибка авторизации.\nНет информации о данном пользователе.\nОбратитесь в отдел АСУ");
		} elseif (!get_param($this->authdata, 'rolename')) {
			$this->preparePopup("Роль пользователя не определена.\nОбратитесь в отдел АСУ.", 'alert-warning');
		} else {
			Session::set('auth', $this->authdata);
		}

		$this->render('', false);
		$this->redirect([
			'location' => '/',
			'soft' => intval($this->authdata === false),
			'delay' => 5,
		]);
		$this->render('');

		return 0;
	}

	public function actionLogout() {

		Session::del('auth');
		Session::destroy();
		setcookie('last-item', null, -1, '/'); // forgot menu item

		$this->redirect('/');
	}

	public function ajaxComplete() {

		$filter = filter_input(INPUT_POST, 'q', FILTER_SANITIZE_STRING);

		// запрос будем строить опираясь на то, что ввели в поисковой строке (число / строка)
		// если это число, то будем искать по табельному номеру, иначе по совпадению ФИО
		$data = $this->model->getUsers($filter, 35);
		echo json_encode($data);
	}
}