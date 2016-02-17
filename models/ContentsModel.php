<?php

/**
 * Created by PhpStorm.
 * User: fellix
 * Date: 27.12.15
 * Time: 23:21
 */
class ContentsModel extends CModel {

	public function getTicketListByStatus($status, $user_department = 0) {

		$depcondition = '';
		$params = [
			'pstate' => $status,
		];

		if ($status == 1) {
			$depcondition = "AND t.department_id = :udep ";
			$params['udep'] = $user_department;
		}

		return $this->select("
		SELECT
		  t.id, t.number, d.name dname, d.id depid,
		  date_format(t.dt_create, '%d.%m.%Y') dc,
		  date_format(t.dt_start, '%d.%m.%Y %H:%i') dstart,
		  date_format(t.dt_stop, '%d.%m.%Y %H:%i') dstop,
		  n.nodename, t.status, a.department_id adep, group_concat(m.name) devs
		FROM bid.tickets t
		  LEFT JOIN bid.departments d ON t.department_id = d.id
		  LEFT JOIN bid.nodes n ON t.node_id = n.id
		  LEFT JOIN bid.agreements a ON t.id = a.ticket_id
		  LEFT JOIN bid.ticket_device td ON t.id = td.ticket_id
		  LEFT JOIN bid.devices m ON td.device_id = m.id
		WHERE t.deleted = 0
		  AND t.status = :pstate
		  $depcondition
		GROUP BY 1,2,3,4,5,6,7,8,9,10
		ORDER BY t.dt_create DESC, t.realtime desc ", $params);
	}

	public function getCounter($udep = 0) {

		return $this->select('
        SELECT st.id, ifnull(q.cnt,0) cnt
		FROM bid.states st LEFT JOIN (
		SELECT o.status, o.cnt FROM (
		SELECT
			status,
			if(t.status = 1, t.department_id, :depid) town,
			count(*)                                  cnt
		FROM bid.tickets t
		WHERE t.deleted = 0
		GROUP BY t.status, 2) o
		WHERE o.town = :depid) q ON st.id = q.status', [
			'depid' => $udep,
		]);
	}
}