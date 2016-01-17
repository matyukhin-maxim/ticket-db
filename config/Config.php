<?php

define('STATUS_DRAFT'     , 1); // Черновик
define('STATUS_AGREE'     , 2); // Согласование цеха
define('STATUS_REVIEW'    , 8); // рассмотрение ГИ
define('STATUS_ACCEPT'    , 3); // Разрешенная ГИ
define('STATUS_OPEN'      , 4); // Открыта ННСом
define('STATUS_COMPLETE'  , 5); // Прикрыта (исполнена)
define('STATUS_REJECT'    , 6); // Отказана (цехом или ГИ)
define('STATUS_CLOSE'     , 7); // закрыта

class Configuration {

	public static $connection = [
		'host' => 'localhost',
		'user' => 'root',
		'pass' => 'fell1x',
		'base' => 'bid',
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

}