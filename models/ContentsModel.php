<?php

/**
 * Created by PhpStorm.
 * User: fellix
 * Date: 27.12.15
 * Time: 23:21
 */
class ContentsModel extends CModel {

	public function getTicketListByStatus($status) {

		return $this->select("
		SELECT
		  t.id, t.number, d.name dname,
		  date_format(t.dt_create, '%d.%m.%Y') dc,
		  date_format(t.dt_start, '%d.%m.%Y %H:%i') dstart,
		  date_format(t.dt_stop, '%d.%m.%Y %H:%i') dstop,
		  n.nodename, t.status
		FROM tickets t
		  LEFT JOIN departments d ON t.department_id = d.id
		  LEFT JOIN nodes n ON t.node_id = n.id
		WHERE t.deleted = 0
		  AND t.status = :pstate
		ORDER BY t.dt_create DESC ", [
			'pstate' => $status,
		]);
	}

	public function getCounter() {

		return $this->select('
		SELECT s.id, ifnull(o.cnt, 0) cnt
		FROM states s
		LEFT JOIN (
			SELECT
				status, count(*) cnt
			FROM tickets t
			WHERE t.deleted = 0
			GROUP BY t.status
		) o ON s.id = o.status');
	}
}