<?php

define('STATUS_DRAFT'     , 1); // Черновик
define('STATUS_AGREE'     , 2); // Согласование цеха
define('STATUS_REVIEW'    , 3); // рассмотрение ГИ
define('STATUS_ACCEPT'    , 4); // Разрешенная ГИ
define('STATUS_OPEN'      , 5); // Открыта ННСом
define('STATUS_COMPLETE'  , 6); // Прикрыта (исполнена)
define('STATUS_REJECT'    , 7); // Отказана (цехом или ГИ)
define('STATUS_CLOSE'     , 8); // закрыта

class Configuration {

	public static $connection = [
		'host' => 'localhost',
		'user' => 'root',
		'pass' => 'fell1x',
		'base' => '',
	];

	public static $scriptList = [
		'lib/jquery.min',
		'lib/jquery-ui.min',
		'lib/jquery.cookie',
		'lib/bootstrap.min',
		'lib/moment.min',
		'lib/i18n/moment-ru',               // rus moment.js
		'lib/bootstrap-select.min',
		'lib/bootstrap-datetimepicker.min', // date & time picker
		'lib/i18n/defaults-ru_RU',          // rus selectpicker
		'lib/ie10-viewport-bug-workaround', // IE10 viewport hack for Surface/desktop Windows 8 bug
		'common',
	];

	public static $siteName  = 'Журнал заявок НГРЭС';
	public static $brandName = 'Журнал заявок Нерюнгринской ГРЭС';

	public static $ROLE_NSS   = "1";
	public static $ROLE_ME    = "2";
	public static $ROLE_USER  = "3";

	public static $passkey = 'qicjlG528b5SOE63cGsFeXKmiZ02Kl32';

}