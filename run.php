<?php

include 'config\Config.php';
include 'core\Session.php';
include 'core\Routine.php';
include 'core\CController.php';

$ctrl = new CController();
// открываем сокет
$errno = 0;
$errstr = '';

$fp = fsockopen('10.82.1.83', 80, $errno, $errstr, 1);
if (!$fp) {
	echo $errno . ' ' . $errstr; // ошибка подключения
}
else {
	// формируем http-заголовки к серверу
	$request  = "HEAD http://nasos2.asu.ngres/ HTTP/1.0\r\n\r\n";
//	$request  = "HEAD / HTTP/1.0\r\n\r\n";

	// отсылаем запрос серверу
	fputs($fp, $request);

	// получем ответ от сервера
	$content = '';
	while(!feof($fp) ){
		$content .= fgets($fp);
	}
	echo $content;
	fclose($fp);
	$ctrl->redirect(['soft'=>1, 'location'=>'/run.php', 'delay'=>1]);
}