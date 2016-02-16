<?php

/**
 * Created by PhpStorm.
 * User: Матюхин_МП
 * Date: 15.02.2016
 * Time: 10:33
 */

/** @property AdministratorModel $model */
class AdministratorController extends CController {

	public function actionIndex() {

		$this->render('index');
	}

	public function actionSyncPassword() {

		$this->render('', false);

		$data = $this->model->getUserList();
		foreach ($data as $user) {

			var_dump($this->model->setUserPassword(get_param($user, 'id'), get_param($user, 'np')));
		}

		$this->render('');
	}

	public function actionAddManyUsers() {

		$persons = array_map('trim', explode("\n", "
			Здоренко Э.Б.
			Греков Е.Г.
			Антонюк Э.П.
			Махмут С.И.
			Кононов А.Е.
			Волгапкин А.И.
			Мацегора А.Л.
			Хоменко Н.В.
			Бакланов Ю.А.
			Тренихин А.В.
			Смирнов П.А.
			Гусев А.П."));

		$this->render('', false);
		$tmod = new TicketModel();
		$result = [];

		foreach ($persons as $user) {
			if (empty($user)) continue;

			$ans = $tmod->findPersonByName($user, false);
			if ($ans) {

				$uid = get_param($ans, 'id', null);
				$fname = get_param($ans, 'fullname');
				$ans[] = $user;
				$ans[] = $this->model->updateUser($uid, $fname, 4, 1);
				$result[] = $ans;
			}
		}

		var_dump($result);

		$this->render('');
	}
}