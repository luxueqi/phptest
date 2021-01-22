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

	public static function ReDirectUrl() {
		/*$r_url = isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : $_SERVER['REQUEST_URI'];

		$r_url = ltrim($r_url, '/');

		if (($i = strpos($r_url, '?')) !== false) {
		$r_url = substr($r_url, 0, $i);
		}*/
		//dump(parse_url($_SERVER['REQUEST_URI'])['path']);
		return ltrim(parse_url($_SERVER['REQUEST_URI'])['path'], '/');
	}

	public static function Csrf() {

		$tmp = self::ServerName();

		if (strpos($tmp, 'http') !== 0) {
			$tmp = self::Host();
		}
		//dump();
		if (!preg_match('/^http(s)?:\/\/' . $tmp . '/', self::Referer())) {
			exitMsg(ErrorConst::API_PARAMS_ERRNO, 'csrf');
		}
	}

}

?>