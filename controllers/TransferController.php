<?php

/**
 * Created by PhpStorm.
 * User: Матюхин_МП
 * Date: 08.02.2016
 * Time: 8:26
 */
class TransferController extends CController {

	/** @var PDO  */
	public $mdb;
	public $alist;

	public function __construct() {
		parent::__construct();

		$basename = 'd:\J_Z_BE.mdb';
		$dns = sprintf('odbc:Driver={Microsoft Access Driver (*.mdb)};Dbq=%s;Uid=Admin',$basename);

		$this->alist = [];

		try {
			$this->mdb = new PDO($dns,'','');
			$this->mdb->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			$this->mdb->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
			//$this->mdb->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			$this->mdb = null;
			var_dump($dns);
			die($e->getMessage());
		}
	}

	private function prepareData(&$stmt) {

		$error = $stmt->errorInfo();
		$ecode = get_param($error, 0);

		if ($ecode !== '00000') {
			$emsg = get_param($error, 2);
			charsetChange($emsg);
			$this->alist[] = "MySQL error [$ecode]: " . ($emsg ? $emsg : 'Invalid params');
			return false;
		}

		$data = $stmt->fetchAll();
		array_walk_recursive($data, 'charsetChange');
		return $data;
	}

	public function actionIndex() {

		$this->render('form', false);

		/*
		$sth = $this->mdb->prepare('select * from nashalniki');
		$sth->execute();

		$data = $sth->fetchAll(PDO::FETCH_NUM);
		array_walk_recursive($data, 'charsetChange');

		$list = [];
		foreach ($data as $row) {
			$list[$row[1]][] = $row[0];
		}

		$sth = $this->mdb->prepare('select * from NSS');
		$sth->execute();
		$data = $sth->fetchAll();
		array_walk_recursive($data, 'charsetChange');

		$list['НСС'] = array_column($data, 'NSS');

		foreach ($list as $dep => $users) {
			echo sprintf("<b>%s</b><br/>", $dep);
			foreach ($users as $person) {
				if (!empty($person))
					echo sprintf("<em>%s</em><br/>", $person);
			}
		}
		*/

		$stmt = $this->mdb->prepare('select * from vivod where dispetcher_o is not null order by 2 desc');
		$stmt->execute();
		$list = $this->prepareData($stmt);



		var_dump($list);
		var_dump($this->alist);

		$this->render('');
	}

	public function actionProcess() {

	}
}