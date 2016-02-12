<?php

/**
 * Created by PhpStorm.
 * User: Матюхин_МП
 * Date: 11.02.2016
 * Time: 10:58
 */
class TransferModel extends CModel {

	public function clearTicketLog($ticket_id) {

		$cnt = 0;
		$this->select('DELETE FROM bid.history WHERE ticket_id = :tid', [
			'tid' => $ticket_id,
		], $cnt);

		return $cnt;
	}

	public function saveNode($node_id, $node_name) {

		$this->select('REPLACE bid.nodes (id, nodename) VALUES (:nid, :nname)', [
			'nid' => $node_id,
			'nname' => $node_name,
		]);
	}

	public function saveDevice($device_id, $device_name, $node_id) {

		$this->select('REPLACE bid.devices (id, node_id, name) VALUES (:did, :nid, :dname)', [
			'did' => $device_id,
			'dname' => $device_name,
			'nid' => $node_id,
		]);
	}

	public function openTicket($ticket_id, $stamp, $f_id = null) {

		$cnt = 0;
		$this->select('UPDATE bid.tickets SET dt_open = :stamp, fire_o = :duser WHERE id = :tid', [
			'tid' => $ticket_id,
			'duser' => $f_id,
			'stamp' => $stamp,
		], $cnt);
		return $cnt === 1;
	}

	public function closeTicket($ticket_id, $stamp, $f_id = null) {

		$cnt = 0;
		$this->select('UPDATE bid.tickets SET dt_close = :stamp, fire_c = :duser WHERE id = :tid', [
			'tid' => $ticket_id,
			'duser' => $f_id,
			'stamp' => $stamp,
		], $cnt);
		return $cnt === 1;
	}

	public function completeTicket($ticket_id, $stamp) {

		$ok = 0;
		$this->select('UPDATE bid.tickets SET dt_close = :stamp WHERE id = :tid', [
			'tid' => $ticket_id,
			'stamp' => $stamp,
		], $ok);
		return $ok !== 0;
	}
}