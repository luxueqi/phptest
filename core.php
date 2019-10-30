<?php
if (!defined('EXITFORBID')) {
	exit('forbid');
}

class Core {

	static public function init() {
		self::setHeader();
		self::setTimezone();
		self::setDebug();
		self::setLoad();

	}

	static function run() {

		//self::init();

		//经过重写,形如/api/wb/login?..... 经过此方法
		$arr = explode('/', $_SERVER['REDIRECT_SCRIPT_URL'], 4);
		unset($arr[0]);
		$f = ucfirst($arr[1]);
		if ($f == 'Api') {
			$f = $f . 'm';
		}
		//var_dump($f);exit;
		//param  要执行的目录 要执行的方法名
		$f::run($arr[2], $arr[3]);

		//exitMsg(ErrorConst::API_ERRNO, 'no api');

	}

	static private function setTimezone() {
		date_default_timezone_set('PRC');
	}

	static private function setHeader() {
		header('Content-type:text/html;charset=utf-8');
	}

	static private function setDebug() {
		if (DEBUG) {
			ini_set('display_errors', 'On');

			error_reporting(E_ALL);

		} else {
			ini_set('display_errors', 'Off');

			//error_reporting(0);

		}
	}

	static private function setLoad() {
		require './lib/function/common.php';

		spl_autoload_register(function ($className) {

			$path = '';

			if (strpos($className, 'Const') !== false) {
				$path = './lib/const/' . $className . '.php';

			} else {
				$path = './lib/class/' . $className . '.class.php';
			}
			if (is_file($path)) {
				require_once $path;
			} else {
				exitMsg(ErrorConst::API_ERRNO, $className . ' not found');
			}

		}, TRUE, TRUE);
	}

}

?>