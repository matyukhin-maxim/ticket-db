<?php

define('STATUS_DRAFT'     , 1); // Черновик
define('STATUS_AGREE'     , 2); // Согласование цеха
define('STATUS_REVIEW'    , 3); // рассмотрение ГИ
define('STATUS_ACCEPT'    , 4); // Разрешенная ГИ
define('STATUS_OPEN'      , 5); // Открыта ННСом
define('STATUS_COMPLETE'  , 6); // Прикрыта (исполнена)
define('STATUS_REJECT'    , 7); // Отказана (цехом или ГИ)
define('STATUS_CLOSE'     , 8); // закрыта
define('STATUS_DELETE'    ,20); // Удаленная заявка (но не скрытая)
define('STATUS_ARCHIVE'   ,90); // Архивная заявка

class Configuration {


	public static $connection = [
		'host' => 'localhost',
		'user' => 'root',
		'pass' => 'fell1x',
		'base' => '',
//		'host' => 'tech-db',
//		'user' => 'bid-user',
//		'pass' => 'bid-user',
//		'base' => 'bid',
	];

	public static $scriptList = [
		'lib/jquery.min',
		'lib/jquery-ui.min',
		'lib/jquery.cookie',
		'lib/bootstrap.min',
		'lib/moment.min',
		'lib/i18n/moment-ru',               // rus moment.js
		'lib/bootstrap-select.min',
		'lib/bootstrap-datetimepicker', // date & time picker
		'lib/i18n/defaults-ru_RU',          // rus selectpicker
		'lib/ie10-viewport-bug-workaround', // IE10 viewport hack for Surface/desktop Windows 8 bug
		'common',
	];

	public static $siteName  = 'Журнал заявок';
	public static $brandName = 'Журнал заявок НГРЭС';

	public static $ROLE_NSS   = "1";
	public static $ROLE_ME    = "2";
	public static $ROLE_USER  = "3";
	public static $ROLE_READ  = "4";
	public static $ROLE_ADMIN = "5";

	public static $passkey = 'qicjlG528b5SOE63cGsFeXKmiZ02Kl32';

}