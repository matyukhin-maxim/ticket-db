<?php

ini_set('display_errors', 1);
set_time_limit(0);
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

chdir(dirname(__FILE__));

require_once 'config/Config.php';
require_once 'core/CModel.php';
require_once 'core/CController.php';
require_once 'core/Routine.php';
require_once 'core/Pager.php';
require_once 'core/Session.php';
require_once 'core/CHtml.php';

$query = strtolower(rtrim(get_param($_GET, 'url', 'index'), '/'));
$url = explode('/', $query);

mb_internal_encoding("UTF-8");
Session::start();

try {

	// подгружаем все файлы моделей (можено бы было в автолоаде, но фтопку)
	foreach (glob('models/*.php') as $model) {
		include_once $model;
	}

	$module = $url[0];

	// проверяем сущевствование файла контролера (класса)
	$file = 'controllers/' . ucfirst($module) . 'Controller.php';
	if (!file_exists($file)) {
		throw new Exception("Файл контроллера '$module' не найден.");
	}

	// подключаем
	require_once $file;

	$module .= 'Controller';
	if (!class_exists($module)) {
		throw new Exception("Класс контроллера '$module' не объявлен.");
	}

	/* @var $ctrl CController */
	$ctrl = new $module();

	// проверим существует ли нужный метод
	$action = get_param($url, 1, 'index');
	$prefix = isAjax() ? 'ajax' : 'action';
	$method = $prefix . ucfirst($action);

	if (!method_exists($ctrl, $method)) {
		throw new Exception("Действие '$method' не определено для контроллера '$module'.");
	}

	// передаем параметры
	$ctrl->arguments = array_slice($url, 2);

	// и вызываем запрошенное действие
	$ctrl->$method();

} catch (Exception $exc) {

	$message = $exc->getMessage();

	setcookie('status', $message, time() + 5, '/');
	$location = get_param($_SERVER, 'HTTP_REFERER', '/');
	if (!isAjax()) header("Location: $location");
	exit();
}
