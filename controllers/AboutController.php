<?php

class AboutController extends CController {

	public function actionIndex() {

		$this->render('help', false);

		var_dump($_SERVER['HTTP_USER_AGENT']);

		$browser = get_browser(null, true);
		var_dump(get_array_part($browser, 'browser majorver'));

		$this->render('');
	}

	public function actionBrowser() {

		$this->render('info');
	}
}