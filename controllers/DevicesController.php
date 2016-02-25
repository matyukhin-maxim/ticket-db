<?php

/**
 * Created by PhpStorm.
 * User: Матюхин_МП
 * Date: 24.02.2016
 * Time: 8:28
 */

/** @property DevicesModel $model */
class DevicesController extends CController {

	public function __construct() {
		parent::__construct();

		if (!$this->authdata) {
			$this->redirect('/auth/');
			return;
		}

		if (!$this->isGrantToMe('ACE_NEW')) {
			$this->preparePopup('Нет доступа к данному разделу');
			$this->redirect(['back' => 1]);
			die();
		}
	}

	public function actionIndex() {

		$this->scripts[] = 'dev-control';

		$this->title = 'Редактирование механизмов';
		$this->data['nodes'] = generateOptions($this->model->getNodes(), null, false);
		$this->render('list');
	}

	/**
	 * Показать список механизмов выбраннго узла (срабатывает при выборе узла из списка)
	 */
	public function ajaxShowList() {

		$node = filter_input(INPUT_POST, 'node', FILTER_VALIDATE_INT) ?: -1;

		$devices = $this->model->getDevices($node);
		$data = '';

		if (count($devices) === 0) {
			$data = CHtml::createTag('div', ['class' => 'alert alert-warning strong text-center'],
				'У выбранного узла нет механизмов');
		} else {
			foreach ($devices as $item) {
				$name = get_param($item, 'name', '?');
				$index = get_param($item, 'id', -1);
				//$mclass = mb_strlen($name) >= 45 ? 'col-xs-12' : 'col-xs-6';
				$data .= CHtml::createTag('div', ['class' => "col-xs-12 list-group-item"], [
					CHtml::createButton('&times', [
						'class' => 'close',
						'title' => 'Удалить',
						'data-id' => $index,
					]),
					$name,
				]);
			}
		}
		echo $data;
	}

	/**
	 * Удаление механзма из узла
	 */
	public function ajaxDeleteDevice() {

		$device = filter_input(INPUT_POST, 'device_id', FILTER_VALIDATE_INT) ?: -1;
		$ok = $this->model->deleteDevice($device);
		if (!$ok) $this->preparePopup($this->model->getErrorList());
		else echo "OK";
	}

	/**
	 * Новый механизм узла
	 */
	public function ajaxNewDevice() {

		$node = filter_input(INPUT_POST, 'node', FILTER_VALIDATE_INT) ?: -1;
		$name = mb_capitalize(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));

		if ($node === -1) {
			$this->preparePopup('Идентификатор узла не указан');
			return;
		} elseif (mb_strlen($name) === 0) {
			$this->preparePopup('Имя механизма не может быть пустым');
			return;
		}

		$id = $this->model->newDevice($node, $name);

		if (count($this->model->getErrors())) {
			$this->preparePopup($this->model->getErrorList());
		} else {
			$this->preparePopup('Механизм добавлен', 'alert-success');
			echo CHtml::createTag('div', ['class' => "col-xs-12 list-group-item"], [
				CHtml::createButton('&times', [
					'class' => 'close',
					'title' => 'Удалить',
					'data-id' => $id,
				]),
				$name,
			]);
		}
	}

	public function ajaxNewNode() {

		$name = mb_capitalize(filter_input(INPUT_POST, 'node', FILTER_SANITIZE_SPECIAL_CHARS));
		if (mb_strlen($name) === 0) {
			$this->preparePopup('Имя узла не может быть пустым!', 'alert-warning');
			return;
		}

		$id = $this->model->newNode($name);
		if (count($this->model->getErrors())) $this->preparePopup($this->model->getErrorList());
		else {
			$this->preparePopup('Новый узел создан', 'alert-success');
			echo CHtml::createOption($name, $id);
		}
	}

	public function ajaxDeleteNode() {

		var_dump($_POST);

		$node = filter_input(INPUT_POST, 'node', FILTER_VALIDATE_INT) ?: -1;

		$cnt = $this->model->deleteNode($node);
		echo $cnt;
		if ($cnt) {
			$this->preparePopup('Узел удален', 'alert-warning');
			echo 'OK';
		} else $this->preparePopup($this->model->getErrorList());
	}
}