<?php

/** @property PDO $db */
class CModel {

	protected static $db        = null;
	protected static $errorlist = [];   // store all errors happening in runtime


	function __construct() {

		if (!$this->isConnected()) {

			$properties = Configuration::$connection;

			try {

				// init mysql connection
				self::$db = new PDO(
					sprintf("mysql:host=%s;dbname=%s", $properties['host'], $properties['base'])
					, $properties['user']
					, $properties['pass']
					, [
					PDO::ATTR_TIMEOUT => 5,
					PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8',
				]);

				self::$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
				//self::$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
				self::$db->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
				self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
				//self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch (Exception $exc) {

				self::$db = null;
				self::$errorlist[] = 'База данных не доступна! ' . $exc->getMessage();
			}
		}
	}

	public function getErrors() {

		return self::$errorlist;
	}

	public static function getErrorList() {

		return join('<br/>', self::$errorlist);
	}

	protected function select($query, $param = array(), &$rowCount = null) {

		if (!self::isConnected()) {
			self::$errorlist[] = 'Связь с БД не установлена.';
			return [];
		}

		/**
		 * модернизируем запрос на лету
		 * если есть параметры в виде массивов
		 * такие параметры будем заменять на конструкцию in
		 *
		 * where field in :[ARRAY] => where field in (x1,x2,...)
		 */

		$cnt = 0;
		foreach ($param as $key => $value) {
			if (gettype($value) === 'array') {
				$condition = "(";
				$local = 0;
				foreach ($value as $item) {

					$condition .= $local ? "," : ""; // если не первый параметр, то добавим запятую
					$vparam = "_X" . ++$cnt;
					$condition .= " :$vparam";

					// а параметр подмассива перекидываем в основной массив
					// елементы вложенного массива не должны быть сами массивами, иначе хрень будет
					$param[$vparam] = $item;
					$local++;
				}
				$condition .= ") ";
				$query = str_replace(":$key", $condition, $query);
				unset($param[$key]);
			}
		}

		//var_dump($param);

		$sth = self::$db->prepare($query);

		foreach ($param as $key => $value) {
			$type = strtolower(gettype($value));
			$cast = null;
			switch ($type) {
				case 'integer':
					$cast = PDO::PARAM_INT;
					break;
				case 'null':
					$cast = PDO::PARAM_NULL;
					break;
				case 'boolean':
					$cast = PDO::PARAM_BOOL;
					break;
				default:
					$cast = PDO::PARAM_STR;
					break;
			}
			$sth->bindValue($key, $value, $cast);
		}

		$sth->execute();
		$error = $sth->errorInfo();
		$ecode = get_param($error, 0);

		if ($ecode !== '00000') {
			$emsg = get_param($error, 2);
			self::$errorlist[] = "MySQL error [$ecode]: " . ($emsg ? $emsg : 'Invalid params');
		}
		$rowCount = $sth->rowCount();
		return $sth->fetchAll();
	}

	public static function isConnected() {

		return self::$db !== null;
	}

	public function startTransaction() {

		self::$db->beginTransaction();
	}

	public function stopTransaction($success = true) {

		if ($success)
			self::$db->commit();
		else
			self::$db->rollBack();
	}

	public function getDB() {
		return self::$db;
	}

}
