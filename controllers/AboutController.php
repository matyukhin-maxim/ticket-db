<?php

class AboutController extends CController {

	public function actionIndex() {

		$this->render('help', false);
		$this->render('');
	}

	public function actionBrowser() {

		$this->render('info');
	}
}