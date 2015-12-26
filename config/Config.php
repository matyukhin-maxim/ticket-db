<?php

class Configuration {

	public static $connection = [
		'host' => 'localhost',
		'user' => 'root',
		'pass' => 'fell1x',
		'base' => 'bid',
	];

	public static $scriptList = [
		'jquery.min',
		'jquery-ui.min',
		'jquery.cookie',
		'bootstrap.min',
		'moment.min',
		'i18n/moment-ru',               // rus moment.js
		'bootstrap-select.min',
		'bootstrap-datetimepicker.min', // date & time picker
		'i18n/defaults-ru_RU',          // rus selectpicker
		'ie10-viewport-bug-workaround', // IE10 viewport hack for Surface/desktop Windows 8 bug
		'common',
	];

	public static $siteName  = 'Журнал заявок НГРЭС';
	public static $brandName = 'Журнал заявок Нерюнгринской ГРЭС';

	public static $ROLE_NSS   = 1;
	public static $ROLE_ME    = 2;
	public static $ROLE_USER  = 3;
	public static $ROLE_ADMIN = 15;

}