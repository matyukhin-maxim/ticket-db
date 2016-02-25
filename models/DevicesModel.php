<?php

/**
 * Created by PhpStorm.
 * User: Матюхин_МП
 * Date: 24.02.2016
 * Time: 10:41
 */
class DevicesModel extends CModel {

	public function getNodes() {

		return $this->select('SELECT id, nodename title FROM bid.nodes WHERE deleted = 0 ORDER BY nodename');
	}

	public function getDevices($node_id) {

		return $this->select('SELECT id, name
          FROM bid.devices
          WHERE deleted = 0 AND node_id = :nid
          ORDER BY name', [
			'nid' => $node_id,
		]);
	}

	public function deleteDevice($device) {

		$result = 0;
		$this->select('UPDATE bid.devices SET deleted = 1 WHERE id = :devid', ['devid' => $device], $result);
		return $result > 0;
	}

	public function newDevice($node, $name) {

		$this->select('INSERT INTO bid.devices (id, node_id, name) VALUES (NULL, :nid, :dname)', [
			'nid' => $node,
			'dname' => $name,
		]);

		return $this->getDB()->lastInsertId();
	}

	public function newNode($nodename) {

		$this->select('INSERT INTO bid.nodes (id, nodename) VALUES (NULL, :nname)', [
			'nname' => $nodename,
		]);

		return $this->getDB()->lastInsertId();
	}

	public function deleteNode($node) {

		$result = 0;
		$this->select('UPDATE bid.nodes SET deleted = 1 WHERE id = :nid', [
			'nid' => $node,
		], $result);

		echo $node;

		return $result;
	}
}