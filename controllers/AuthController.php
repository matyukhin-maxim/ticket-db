<?php

/** @property AuthModel $model */
class AuthController extends CController {

	public function actionIndex() {

		Session::del('auth');
		Session::destroy();
		$this->scripts[] = 'authorisation';
		$this->render('form');
		//$this->redirect('http://auth-server.asu.ngres/');
	}

	public function actionLogin() {

		$login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_STRING);
		$password = filter_input(INPUT_POST, 'password');

		$authdata = $this->model->setAuthenticate($login, $password);
		if (!$authdata) {
			$this->prepareError('Ошибка авторизации. Пользователь или пароль указаны не верно.');
		} elseif (!get_param($authdata, 'rolename')) {
			$this->prepareError('Роль указаннго пользователя не определена. Обратитесь в отдел АСУ.');
		} else {
			Session::set('auth', $authdata);
		}

		$this->redirect('/');
	}

	public function ajaxLogin() {

		$login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_STRING);
		$password = filter_input(INPUT_POST, 'password');

		$this->authdata = $this->model->setAuthenticate($login, $password);
		if (!$this->authdata) {
			$this->prepareError("Ошибка авторизации.\nПользователь или пароль указаны неверно.");
		} elseif (!get_param($this->authdata, 'rolename')) {
			$this->prepareError("Роль пользователя не определена.\nОбратитесь в отдел АСУ.", 'alert-warning');
		} else {
			Session::set('auth', $this->authdata);
			return;
		}

		echo 'error';
	}

	public function actionLogout() {

		Session::del('auth');
		Session::destroy();
		setcookie('last-item', null, -1, '/'); // forgot menu item

		$this->redirect('/');
	}
}