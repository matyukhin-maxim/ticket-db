<?php

class AuthModel extends CModel {

	public function setAuthenticate($p_login, $p_password) {

		$auth = $this->select('
		SELECT
			u.id, u.fullname, u.role_id, u.department_id,
			u.login, r.rolename, d.name depname
		FROM users u
		LEFT JOIN roles r ON u.role_id = r.id
		LEFT JOIN departments d ON d.id = u.department_id
		WHERE u.deleted = 0
			AND u.login = :login
			AND u.pwd_hash = :password', [
			'login' => $p_login,
			'password' => sha1($p_password),
		]);

		// если пользователь не найден, то пытаться определить его првава нет никакого смысла
		if (!$auth) return false;

		$data = get_param($auth, 0);
		return $data;
	}
}
