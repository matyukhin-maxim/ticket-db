<?php

/**
 * Created by PhpStorm.
 * User: Матюхин_МП
 * Date: 15.02.2016
 * Time: 10:36
 */
class AdministratorModel extends CModel {

	public function getUserList() {

		return $this->select('
			SELECT u.*, o.id uid, o.pwd_hash np
			FROM bid.users u
			JOIN oper.users o ON u.fullname = oper.get_user_full(o.id)');
	}

	public function setUserPassword($uid, $pass = 123) {

		$res = 0;
		$this->select('UPDATE bid.users SET pwd_hash = :pwd WHERE id = :uid', [
			'uid' => $uid,
			'pwd' => $pass,
		], $res);

		return $res === 1;
	}

	public function updateUser($uid, $fname, $role, $depid) {

		$res = 0;
		$this->select('
			REPLACE INTO bid.users (id, login, fullname, role_id, department_id)
			VALUES (:uid, :login, :fname, :rid, :depid)', [
			'uid' => $uid,
			'login' => makeSortName($fname),
			'fname' => $fname,
			'rid' => $role,
			'depid' => $depid,
		], $res);

		return $res !== 0;
	}
}