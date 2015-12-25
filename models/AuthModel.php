<?php

class AuthModel extends CModel {

	public function setAuthenticate($p_login, $p_password) {

		$auth = $this->select('
		select
			u.id, u.fullname, u.role_id, u.department_id,
			u.login, r.rolename, d.name depname
		from users u
		left join roles r on u.role_id = r.id
		left join departments d on d.id = u.department_id
		where u.deleted = 0
			and u.login = :login
			and u.pwd_hash = :password', [
			'login' => $p_login,
			'password' => sha1($p_password),
		]);

		// если пользователь не найден, то пытаться определить его првава нет никакого смысла
		if (!$auth) return false;

		$data = get_param($auth, 0);
		return $data;
	}
}
