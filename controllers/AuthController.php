<?php

/** @property AuthModel $model */
class AuthController extends CController {

	public function actionIndex() {

		Session::del('auth');
		$this->render('form');
	}

	public function actionLogin() {

		$this->authdata = false;

		$login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_STRING);
		$password = filter_input(INPUT_POST, 'password');

		$authdata = $this->model->setAuthenticate($login, $password);
		if (!$authdata) {

			setcookie('status', 'Ошибка авторизации. Пользователь или пароль указаны не верно.', time() + 5, '/');
		} elseif (!get_param($authdata, 'rolename')) {

			setcookie('status', 'Роль указаннго пользователя не определена. Обратитесь в отдел АСУ.', time() + 5, '/');
		} else {

			Session::set('auth', $authdata);
		}

		$this->redirect('/');
	}

	public function actionLogout() {

		Session::del('auth');
		Session::destroy();
		setcookie('last-item', null, -1, '/'); // forgot menu item

		$this->redirect('/');
	}
}