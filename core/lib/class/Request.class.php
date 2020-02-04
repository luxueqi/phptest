<?php
if (!defined('EXITFORBID')) {
	exit('forbid');
}
/**
 *
 */
class Request {

	public static function Referer() {

		return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
	}

	public static function UserAgent() {
		return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
	}

	public static function RemoteAddr() {
		return $_SERVER['REMOTE_ADDR'];
	}

	public static function Host() {
		return $_SERVER['HTTP_HOST'];
	}

	public static function ServerName() {
		return $_SERVER['SERVER_NAME'];
	}

	public static function Csrf() {
		if (!preg_match('/^http(s)?:\/\/' . self::ServerName() . '/', self::Referer())) {
			exitMsg(ErrorConst::API_PARAMS_ERRNO, 'csrf');
		}
	}

}

?>