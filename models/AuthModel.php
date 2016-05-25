<?php

class AuthModel extends CModel {

	public function setAuthenticate($p_login, $p_password) {

		$auth = $this->select('
		SELECT
			u.id, u.fullname, u.role_id, u.department_id depid,
			u.login, r.rolename, d.name depname
		FROM bid.users u
		LEFT JOIN bid.roles r ON u.role_id = r.id
		LEFT JOIN bid.departments d ON d.id = u.department_id
		WHERE u.deleted = 0
			AND u.id = :login
			AND u.pwd_hash = :password', [
			'login' => $p_login,
			'password' => sha1($p_password),
		]);

		// если пользователь не найден, то пытаться определить его првава нет никакого смысла
		if (!$auth) return false;

		$data = get_param($auth, 0);
		return $data;
	}

	public function openAuth($p_login) {

		$auth = $this->select('
		SELECT
			u.id, u.fullname, u.role_id, u.department_id depid,
			u.login, r.rolename, d.name depname
		FROM bid.users u
		LEFT JOIN bid.roles r ON u.role_id = r.id
		LEFT JOIN bid.departments d ON d.id = u.department_id
		WHERE u.deleted = 0
			AND u.id = :login', [
			'login' => $p_login,
		]);

		// если пользователь не найден, то пытаться определить его првава нет никакого смысла
		if (!$auth) return false;

		$data = get_param($auth, 0);
		return $data;
	}

	public function getUsers($filter, $limit = 0) {

		if (empty($filter)) return [];
		$field = 'id';

		if (is_numeric($filter)) {
			$filter = "8000$filter%";
		} else {
			$filter .= '%';
			$field = 'fullname';
		}

		$limit = $limit ? sprintf("LIMIT %d ", intval($limit)) : '';
		$result = $this->select("
        SELECT
          id       value,
          fullname label
        FROM bid.users
        WHERE $field LIKE :filter
              AND deleted = 0
        ORDER BY fullname
        $limit
        ", ['filter' => $filter]);

		return $result;
	}

}
