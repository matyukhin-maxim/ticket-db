<?php

class IndexController extends CController {

	public function actionIndex() {

		$sid = get_param($_REQUEST, 'PHPSESSID');
		if ($sid) {

			Session::destroy();
			setcookie("PHPSESSID", $sid, time() + 3600 * 2, '/');
		}
		$this->redirect('/contents/');
	}

}
