<?php

/**
 * Created by PhpStorm.
 * User: Матюхин_МП
 * Date: 08.02.2016
 * Time: 8:26
 */

/** @property TransferModel $model */
class TransferController extends CController {

	/** @var PDO */
	public $mdb;
	public $qerrors;

	public function __construct() {
		parent::__construct();

		$basename = 'd:\OLD_MDB\J_Z_BE.mdb';
		$dns = sprintf('odbc:Driver={Microsoft Access Driver (*.mdb)};Dbq=%s;Uid=Admin', $basename);

		$this->qerrors = [];

		try {
			$this->mdb = new PDO($dns, '', '');
			$this->mdb->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			$this->mdb->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
			//$this->mdb->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			$this->mdb = null;
			var_dump($dns);
			die($e->getMessage());
		}
	}

	/**
	 * @param $stmt PDOStatement
	 * @return array
	 */
	private function prepareData(&$stmt) {

		$error = $stmt->errorInfo();
		$ecode = get_param($error, 0);

		if ($ecode !== '00000') {
			$emsg = get_param($error, 2);
			charsetChange($emsg);
			$this->qerrors[] = "ACCESS error [$ecode]: " . ($emsg ? $emsg : 'Invalid params');
			return false;
		}

		$data = $stmt->fetchAll();
		array_walk_recursive($data, 'charsetChange');
		return $data;
	}

	public function actionIndex() {

		$this->render('', false);


		$stmt = $this->mdb->prepare('
		select * from vivod
		where delete = 0
			and DataPodashi >= :ds
		order by 2 desc');
		$stmt->execute([
			'ds' => '2016-01-01',
		]);

		//and NomerZaiavki in (11677,11653)

		$list = $this->prepareData($stmt);
		$tdev = $this->mdb->prepare('select * from RemontMehanizmov where NomerZaiavki = :tnum');
		//$list = array_slice($list, 0, 13);
		//var_dump($list);

		$tmod = new TicketModel();


		$nlist = array_column($tmod->getNodes(), 'id', 'title');
		$nlist['аккумуляторные батареи'] = 1;       // Исключение (pretty view)
		$dlist = array_column($tmod->getDepartments(), 'id', 'title');
		$dlist['Электроцех'] = $dlist['ЭЦ'];        // Теперь это просто ЭЦ
		$dlist['ЦСДТУ'] = $dlist['СДТУ'];        // Теперь это ...
		$dlist['ОИТ'] = $dlist['ОАСУ'];        // Теперь это ...
		//var_dump($dlist);

		$fire_d = array_column($tmod->getFireDispatcher(), 'id', 'title');
		//var_dump($fire_d);

		$user_cache = [];
		$user_cache['Ернмеев С.Н.'] = '80000075'; // Исключение (опечатка)
		$user_cache['Еремеев С.Н.'] = '80000075'; // Исключение (два человека)
		$user_cache['Кальнер Б.В.'] = '80000791'; // Мой косяк (превращаем в Боровика)

		$results = [
			'Отказать' => 3,
			'Разрешить' => 1,
			'Разрешить при условии' => 2,
		];

		$dev_cache = []; // кэш устройств чтобы постоянно не лазить в базу

		foreach ($list as $ticket) {

			$info = [];
			$ticket_id = get_param($ticket, 'NomerZaiavki');
			$info['ticket_id'] = $info['t_number'] = $ticket_id;
			$info['depid'] = get_param($dlist, get_param($ticket, 'ZECH', 0));

			// Дата создания
			$date = explode(' ', join(' ', get_array_part($ticket, 'DataPodashi TimePodashi')));
			$info['t_cdate'] = join(' ', get_array_part($date, [0, 3]));
			if (empty(get_param($ticket, 'TimePodashi'))) continue;

			// Дата начала
			$date = explode(' ', join(' ', get_array_part($ticket, 'Data_Zayavki1 Times_Zayavki1')));
			$info['td_start'] = sqldate2human(join(' ', get_array_part($date, [0, 3])));

			// Дата конца
			$date = explode(' ', join(' ', get_array_part($ticket, 'Data_Zayavki2 Times_Zayavki2')));
			$info['td_stop'] = sqldate2human(join(' ', get_array_part($date, [0, 3])));

			$creator = trim(get_param($ticket, 'FamilijaPod'));
			if (empty($creator)) continue;
			$info['user'] = get_param($user_cache, $creator) ?: $tmod->findPersonByName($creator);
			$user_cache[$creator] = $info['user'];

			$info['t_message'] = get_param($ticket, 'ZAIABKA') ?: 'Нечего сказать...';
			$info['t_node'] = get_param($nlist, get_param($ticket, 'Oborudovanie'), -1);
			if ($info['t_node'] === -1) var_dump(get_array_part($ticket, 'NomerZaiavki Oborudovanie'));

			$this->model->clearTicketLog($ticket_id); // Удаляем лог
			//$tmod->setTicketStatus($ticket_id, STATUS_CLOSE, $info['user']);
			//$tmod->setTicketStatus($ticket_id, STATUS_DRAFT, $info['user']);

			// ремонтируемые устройства
			$node_id = $info['t_node'];
			$tdev->execute(['tnum' => $ticket_id]);
			$devices = array_column($this->prepareData($tdev), 'MEHANIZM');
			if (count($devices)) {
				// Получаем список устройств указанного узла,
				// и пытаемся сопоставить по имени
				// выполнив некоторые преобразования (pretty | trim ucfirst)

				$bdev = get_param($dev_cache, $node_id) ?: array_column($tmod->getDevices($node_id), 'id', 'name');
				$dev_cache[$node_id] = $bdev;

				foreach ($devices as $cdev) {
					$did = get_param($bdev, mb_capitalize($cdev));
					if ($did) $info['devices'][] = $did;
				}

				if (count($devices) != count($info['devices'])) {
					var_dump("!!! " . $ticket_id . " !!!", $devices, $node_id, $bdev);
				}
			}

			$info['status'] = STATUS_DRAFT;

			// Согласование цеха
			$adep = get_param($ticket, 'SoglasZech', -1);
			$agree = [];
			if ($adep !== null) {

				$agree['id'] = $ticket_id;
				$agree['depid'] = get_param($dlist, $adep, null);
				$info['status'] = STATUS_AGREE;

				switch (get_param($ticket, 'Soglasie')) {
					case 'согласовано' :
						$agree['result'] = 1;
						$info['status'] = STATUS_REVIEW;
						break;
					case 'не согласовано' :
						$agree['result'] = 0;
						$info['status'] = STATUS_REJECT;
						break;
					default:
						$agree['result'] = null; //Ждем согласования
				}
				$agree['user'] = null;
				if (is_int($agree['result'])) {
					$auser = get_param($ticket, 'SoglasFamilia', -1);
					$agree['user'] = get_param($user_cache, $auser) ?: $tmod->findPersonByName($auser);
					$user_cache[$auser] = $agree['user'];
				}

				$info['agreement'] = $agree['depid'];
				//var_dump($agree);
			}

			// @todo Тут как бы по хорошему надо сохранить заявку
			$tmod->saveTicket($info);
			$tmod->setTicketStatus($ticket_id, STATUS_DRAFT, $info['user'], $info['t_cdate']);
			if ($adep !== null) {
				$tmod->setAgreement($ticket_id, $agree['depid'], $agree['user'], $agree['result'], null);
				$tmod->setTicketStatus($ticket_id, $info['status'], $agree['user'], $info['t_cdate']);
			} else $tmod->setTicketStatus($ticket_id, STATUS_REVIEW, $info['user'], $info['t_cdate']);

			// Разрешение главным инженером
			$r_result = get_param($ticket, 'Razreshenie');
			if ($r_result !== null) {

				$resolution = [];
				$resolution['id'] = $ticket_id;
				$resolution['result'] = $results[$r_result];
				$resolution['reason'] = get_param($ticket, 'Pozit', null);
				$engineer = get_param($ticket, 'Familia2');
				$resolution['user'] = get_param($user_cache, $engineer) ?: $tmod->findPersonByName($engineer);
				$user_cache[$engineer] = $resolution['user'];

				$info['status'] = $resolution['result'] === 3 ? STATUS_REJECT : STATUS_ACCEPT;

				$tmod->setConfirmation($ticket_id, $resolution['user'], $resolution['result'], $resolution['reason']);
				$tmod->setTicketStatus($ticket_id, $info['status'], $resolution['user'], $info['t_cdate']);
				//var_dump($resolution,$r_result);
			}

			// Открытие заявки
			$dopen = get_param($ticket, 'DataVivoda');
			if ($dopen) {

				$ouser = get_param($ticket, 'Familia');
				$person = get_param($user_cache, $ouser) ?: $tmod->findPersonByName($ouser);
				$user_cache[$ouser] = $person;

				$fid = get_param($fire_d, get_param($ticket, 'Dispetcher_O') ?: -1, null);
				$this->model->openTicket($ticket_id, $dopen, $fid);
				$tmod->setTicketStatus($ticket_id, STATUS_OPEN, $person, $dopen);
			}

			// Прикрытие заявки
			$dcomplete = get_param($ticket, 'DataZakritija');
			if ($dcomplete) {

				$ouser = get_param($ticket, 'FamiliaZakr');
				$person = get_param($user_cache, $ouser) ?: $tmod->findPersonByName($ouser);
				$user_cache[$ouser] = $person;

				$this->model->completeTicket($ticket_id, $dcomplete);
				$tmod->setTicketStatus($ticket_id, STATUS_COMPLETE, $person, $dcomplete);
			}

			// Закрытие заявки
			$dclose = get_param($ticket, 'TimesVvoda');
			if ($dclose) {

				$ouser = get_param($ticket, 'FamiliaVvod');
				$person = get_param($user_cache, $ouser) ?: $tmod->findPersonByName($ouser);
				$user_cache[$ouser] = $person;

				$fid = get_param($fire_d, get_param($ticket, 'Dispetcher_V') ?: -1, null);
				$this->model->closeTicket($ticket_id, $dclose, $fid);
				$tmod->setTicketStatus($ticket_id, STATUS_CLOSE, $person, $dclose);
			}

			//var_dump($info);
			//$tmod->setTicketStatus($ticket_id, STATUS_CLOSE, -1);
		}

		//var_dump($dev_cache);
		var_dump($user_cache);

		if (count($this->qerrors)) var_dump($this->qerrors);

		$this->render('');
	}

	public function actionSyncDevice() {

		$st = $this->mdb->prepare("select Oborudovanie, MEHANIZM from mehanizm order by 1, 2");
		$st->execute();
		$data = $this->prepareData($st);

		$result = [];
		foreach ($data as $device) {
			$node = mb_capitalize(get_param($device, 'Oborudovanie'));
			$device = mb_capitalize(get_param($device, 'MEHANIZM'));
			$result[$node][] = $device;
		}

		$nid = 0; $did = 0;
		$this->model->startTransaction();
		foreach ($result as $node => $deviceList) {
			$this->model->saveNode(++$nid, $node);
			foreach ($deviceList as $dname) $this->model->saveDevice(++$did, $dname, $nid);
		}
		$this->model->stopTransaction();

		var_dump($this->model->getErrors());
	}

	public function actionSyncOperNames () {

		$this->render('', false);
		$data = $this->model->getBadNames();
		var_dump(count($data));
		$tmod = new TicketModel();

		$cache = [];
		$result = 1;
		$this->model->startTransaction();

		foreach ($data as $user) {

			$short = trim(join(' ', get_array_part($user, 'lname fname pname')));
			//var_dump($short);

			$full = $tmod->findPersonByName($short, 'fullname');
			if ($full) {

				$uid = get_param($user, 'id');
				$cache[$uid] = $full;

				$result *= $this->model->setOperName($uid, $full);
			}
		}

		if ($result) var_dump($cache);
		$this->model->stopTransaction($result);

		$this->render('');
	}

	public function actionSetOperTabel() {

		$this->render('', false);
		$personal = $this->model->getAllOperNames();

		$tmod = new TicketModel();

		$cache = [];
		$result = 1;
		$this->model->startTransaction();

		foreach ($personal as $user) {

			//$short = trim(join(' ', get_array_part($user, 'lname fname pname')));
			$short = get_param($user, 'lname');
			$short .= ' ' . mb_substr(get_param($user, 'fname'), 0, 1);
			$short .= ' ' . mb_substr(get_param($user, 'pname'), 0, 1);

			$full = $tmod->findPersonByName($short, 'id');
			if ($full) {
				$uid = get_param($user, 'id');
				$result *= $this->model->setOperTabel($uid, $full);
			} else {
				$cache[] = $short;
			}
		}

		//if ($result)
		var_dump($cache);
		$this->model->stopTransaction($result);

		$this->render('');
	}
}