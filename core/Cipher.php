<?php

/**
 * Created by PhpStorm.
 * User: Матюхин_МП
 * Date: 25.05.2016
 * Time: 7:56
 */
class Cipher {

	public static $method = 'aes-256-ctr';

	public static function encode($data, $secret, $serialize = true) {

		if ($serialize) $data = serialize($data);
		$crypt = openssl_encrypt(
			$data,
			self::$method,
			$secret,
			OPENSSL_ZERO_PADDING,
			substr(sha1('ngres'), 0, 16)
		);

		return strtr($crypt, '+/=', '-,_');
	}

	public static function decode($data, $secret, $serialize = true) {

		$data = strtr($data, '-,_', '+/=');

		$plain = openssl_decrypt(
			$data,
			self::$method,
			$secret,
			OPENSSL_ZERO_PADDING,
			substr(sha1('ngres'), 0, 16)
		);

		return $serialize ? unserialize($plain) : $plain;
	}

	public static function generate_token($len = 32) {

		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		//$chars = str_shuffle($chars);
		$nc = strlen($chars) - 1;

		$token = '';
		for ($idx = 0; $idx < $len; $idx++) $token .= $chars[mt_rand(0, $nc)];
		return $token;
	}
}