<?php

/**
 * Created by PhpStorm.
 * User: Матюхин_МП
 * Date: 22.12.2015
 * Time: 15:19
 */
class TicketModel extends CModel {

	public function getDepartments() {

		return $this->select('SELECT id, name title
          FROM departments
          WHERE deleted = 0
          ORDER BY name');
	}

	public function getNodes() {

		return $this->select('SELECT id, nodename title
          FROM nodes
          WHERE deleted = 0
          ORDER BY nodename');
	}

	public function getDevices($node_id) {

		return $this->select('SELECT id, name
          FROM devices
          WHERE deleted = 0 AND node_id = :nid
          ORDER BY name', [
			'nid' => $node_id,
		]);
	}

	public function saveTicket($arguments) {

		$query = "REPLACE INTO tickets
			(id, dt_create, number, dt_start, dt_stop, user_id, department_id, node_id, message)
			VALUES
  			(:tid, :dt_create, :tnumber, :dt_start, :dt_stop, :uid, :depid, :tnode, :tmessage)";

		$this->startTransaction();

		$tnum = get_param($arguments, 't_number');
		$this->select($query, [
			'tid' => get_param($arguments, 'ticket_id'),
			'tnumber' => $tnum ?: $this->getNextTicketNumber(),
			'dt_start' => date2mysql(get_param($arguments, 'td_start')),
			'dt_stop' => date2mysql(get_param($arguments, 'td_stop')),
			'tmessage' => get_param($arguments, 't_message', 'Текст заявки не указан.'),
			'tnode' => get_param($arguments, 't_node'),
			'uid' => get_param($arguments, 'user'),
			'depid' => get_param($arguments, 'depid'),
			'dt_create' => get_param($arguments, 't_cdate'),
		]);

		// получаем идентификатор созданного/измененного тикета
		$ticketID = get_param($arguments, 'ticket_id') ?: $this->getDB()->lastInsertId();

		// сохраним используемые меанизмы
		$this->select("UPDATE ticket_device SET deleted = 1 WHERE ticket_id = :tid", [
			'tid' => $ticketID,
		]);
		$devlist = get_param($arguments, 'devices', []);
		foreach ($devlist as $device) {
			$this->select('REPLACE INTO ticket_device (ticket_id, device_id, deleted) VALUES (:tid, :dev_id, 0)', [
				'tid' => $ticketID,
				'dev_id' => $device,
			]);
		}

		// сохраним информацию о согласовании
		$this->select('UPDATE agreements SET deleted = 1 WHERE ticket_id = :tid', [
			'tid' => $ticketID,
		]);
		if (get_param($arguments, 'agreement')) {
			$this->select('REPLACE agreements (ticket_id, department_id, deleted) VALUES (:tid, :depid, 0)', [
				'tid' => $ticketID,
				'depid' => get_param($arguments, 'agreement'),
			]);
		}

		$ok = count($this->getErrors()) == 0;
		$this->stopTransaction($ok);
		return $ok ? $ticketID : null;
	}

	public function getNextTicketNumber() {

		$data = $this->select('SELECT ifnull(max(number),0) + 1 np FROM tickets');
		$number = get_param($data, 0);
		return get_param($number, 'np', 1);
	}

	public function getTicketInfo($ticket_id) {

		// получим всю информацию о заявке
		$info = $this->select('SELECT * FROM tickets WHERE id = :tid AND deleted = 0', [
			'tid' => $ticket_id,
		]);
		$data = get_param($info, 0);
		if (!$data) return false;

		// и список устройств учавствующих в ней
		$devs = $this->select('SELECT * FROM ticket_device WHERE ticket_id = :tid AND deleted = 0', [
			'tid' => $ticket_id,
		]);
		$data['devices'] = array_column($devs, 'device_id');

		// а также информацию о согласовании и разрешении
		$row = $this->select('
			SELECT a.department_id, a.dt_stamp, u.fullname, a.result, a.reason
			FROM agreements a
			  LEFT JOIN users u ON a.user_id = u.id
			WHERE a.ticket_id = :tid AND a.deleted = 0', [
			'tid' => $ticket_id,
		]);
		$data['agree'] = get_param($row, 0);

		$row = $this->select("
			SELECT dt_stamp, u.fullname, r.reason,
        case r.result
          when 1 then 'Разрешено'
          when 2 then 'Разрешено при условии'
          when 3 then 'Отказано'
          else '-'
        end result
			FROM resolutions r
			  LEFT JOIN users u ON r.user_id = u.id
			WHERE r.ticket_id = :tid AND r.deleted = 0", [
			'tid' => $ticket_id,
		]);
		$data['review'] = get_param($row, 0);

		return $data;
	}

	public function getUserName($user_id) {

		$data = $this->select('SELECT fullname, department_id depid FROM users WHERE id = :uid', [
			'uid' => $user_id,
		]);

		return get_param($data, 0);
	}

	public function deleteDraft($ticket_id) {

		$sth = $this->getDB()->prepare('UPDATE tickets SET deleted = 1 WHERE id = :tid');
		$sth->execute(['tid' => $ticket_id,]);

		return $sth->rowCount();
	}

	public function setTicketStatus($ticket_id, $status, $user_id = null) {

		if (!$user_id) {
			$auth = Session::get('auth');
			$user_id = get_param($auth, 'id');
		}

		$this->select('UPDATE tickets SET status = :state WHERE id = :tid', [
			'tid' => $ticket_id,
			'state' => $status,
		]);

		$this->select('INSERT INTO history
			(ticket_id, user_id, dt_stamp, status_id)
		VALUES (:tid, :uid, now(), :state)', [
			'tid' => $ticket_id,
			'uid' => $user_id,
			'state' => $status,
		]);
	}

	public function setAgreement($ticket_id, $dep, $user_id, $result, $reason) {

		$this->select('REPLACE INTO agreements
			(ticket_id, department_id, dt_stamp, user_id, result, reason)
			VALUES (:tid, :depid, now(), :uid, :result, :reason) ', [
			'tid' => $ticket_id,
			'depid' => $dep,
			'uid' => $user_id,
			'result' => $result,
			'reason' => $reason,
		]);
	}

	public function setConfirmation($ticket_id, $user_id, $result, $reason) {

		$this->select('REPLACE INTO resolutions
			(ticket_id, dt_stamp, user_id, result, reason)
			VALUES (:tid, now(), :uid, :result, :reason) ', [
			'tid' => $ticket_id,
			'uid' => $user_id,
			'result' => $result,
			'reason' => $reason,
		]);
	}
}