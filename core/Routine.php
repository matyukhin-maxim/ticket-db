<?php

/**
 * Get some value from input array
 * by key, or self $source, if key is null
 * if source[key] is undefined then deturn default value
 *
 * @param mixed $source
 * @param string|null $key
 * @param mixed $def
 * @return mixed
 */
function get_param($source, $key = null, $def = false) {
	if ($key === null) {
		return isset($source) ? $source : $def;
	}
	return array_key_exists($key, $source) ? $source[$key] : $def;
}

/**
 * Change codepege
 *
 * @param string $value
 */
function charsetChange(&$value) {
	$type = gettype($value);
	if ($type === 'string')
		$value = mb_convert_encoding($value, 'UTF-8', 'Windows-1251');
}

/**
 * Функция возвращает переданный ей многострочный текст,
 * убрав лишние пробелы в каждой подстроке
 * (т.к. при копипасте с word`а нсс копируют ТАБы)
 *
 * @param $txt
 * @return string
 */
function trimHereDoc($txt) {
	return implode("\n", array_map('trim', explode("\n", $txt)));
}

/**
 * Check that incoming request is POST method (form or ajax)
 *
 * @return boolean
 */
function isPOST() {
	return get_param($_SERVER, 'REQUEST_METHOD') === 'POST';
}

/**
 * little check for CController
 * diffrent actions will be run depends of this func
 *
 * @return boolean
 */
function isAjax() {
	return get_param($_SERVER, 'HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest';
}

/**
 * turn input parameter(string) into mysql like-string
 * 'blah blah' => '%blah blah%'
 *
 * @param string $value
 * @return string
 */
function toLike($value) {

	$ans = '%';
	$type = gettype($value);
	switch ($type) {
		case 'string':

			if (!empty($value)) {
				$ans .= "$value%";
			}
			break;

		default:
			break;
	}
	return $ans;
}

/**
 *
 * convert text string from DOS codepage into UTF
 *
 * @param string $text
 * @return string
 */
function d2u($text) {
	return iconv('cp866', 'utf-8', $text);
}

/**
 * Возвращает часть массива по списку ключей
 * передынных в виде строки или массива
 *
 * @param array $data
 * @param string|array $keys
 * @param boolean $addempty
 *
 * @return array
 */
function get_array_part($data, $keys, $addempty = false) {

	$result = [];

	if (!is_array($keys)) {
		$keys = explode(' ', $keys);
	}

	foreach ($keys as $key) {
		$value = get_param($data, $key);
		if ($value || $addempty)
			$result[] = $value;
	}

	return $result;
}

/**
 * Генерирует опции для тега select
 * переданного в виде массива.
 *
 * @param $options array Массив опций с ключами id и title
 * @param int $selected int|string Предварительно выбранное значение
 * @param bool $default boolean Нужно ли добавить к опциям пустой параметр по-умолчанию
 * @return string
 */
function generateOptions($options, $selected = 0, $default = true) {
	if (!is_array($options))
		$options = array($options);

	$result = "";
	if ($default)
		$result .= sprintf('<option value="">%s</option>', is_string($default) ? $default : '-') . PHP_EOL;

	foreach ($options as $item) {
		$id = get_param($item, 'id', 0);
		$result .= sprintf('<option value="%s" %s>%s</option>',
				$id,
				$id == $selected ? 'selected' : '',
				get_param($item, 'title', '?')) . PHP_EOL;
	}

	return $result;
}


/**
 * callback-конвертор даты из формата dd.mm.yyyy hh:mm
 * в дату, корректную для вставки в mysql базу
 *
 * @param $userDate
 * @return string
 */
function date2mysql($userDate) {
	$date = date_create_from_format('d.m.Y H:i', $userDate);
	return $date ? date_format($date, 'Y-m-d H:i') : date('Y-m-d H:i');
}


/**
 * Преобрразование даты выбранной из mysql базы
 * в нужный формат
 *
 * @param string $dbdate database date
 * @param string $format
 * @return string
 */
function sqldate2human($dbdate, $format = "d.m.Y H:i") {
	$date = date_create_from_format('Y-m-d H:i:s', $dbdate);
	return $date ? date_format($date, $format) : date($format);
}

/**
 * Функция преобразования полного ФИО
 * в короткое представление (Фамилия и инициалы) для отображения
 * на странице, где нет много свободного места
 *
 * @param string $fullname
 * @return string
 */
function makeSortName($fullname) {

	$parts = preg_split('/\s+/', $fullname);
	$result = '';
	foreach ($parts as $next) $result .= ' ' . ($result ? mb_substr($next, 0, 1) . '.' : $next);
	return trim($result);
}