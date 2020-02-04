<?php
if (!defined('EXITFORBID')) {
	exit('forbid');
}

class Core {

	static public function init() {
		self::setHeader();
		self::setTimezone();
		self::setDebug();
		self::setConstPath();
		self::setLoad();
		self::setSession();

	}

	static function run() {

		self::init();

		//经过重写,形如/api/wb/login?..... 经过此方法
		//var_dump($_SERVER);exit;
		if (isset($_SERVER['REDIRECT_URL'])) {
			$arr = [];
			$r_url = ltrim($_SERVER['REDIRECT_URL'], '/');
			if (strpos($r_url, '-') !== false) {
				$arr = explode('-', $r_url);
			} else {
				$arr = explode('/', $r_url);
			}
			define('__M__', $arr[0]);
			define('__C__', $arr[1]);
			define('__A__', str_replace('.html', '', $arr[2]));

			$path = './' . __M__ . '/' . __C__ . '/Api.php';

			if (is_file($path)) {
				require_once $path;
				$ee = 'no api';

				if (in_array(__A__, get_class_methods('Api'))) {
					$a = __A__;

					try {
						(new Api)->$a();
						return;
					} catch (Exception $e) {
						$ee = 'api err';
						if (DEBUG == true) {
							$ee = "Exception " . $e->getCode() . " :" . $e->getMessage() . " in File " . $e->getFile() . " on line " . $e->getLine();
						} else {
							header('HTTP/1.1 500 Internal Server Error');
							exit;

						}

					}

					//return;
				}

			}

			if (isGetPostAjax('get') && !DEBUG) {
				header("HTTP/1.1 404 Not Found");
				header("Status: 404 Not Found");
				include ROOT_PATH . '/public/view/wsign/404.html';
				exit;
			}

			exitMsg(ErrorConst::API_ERRNO, $ee, [__M__, __C__, __A__]);

		}

	}
	private static function setSession() {
		session_name('wsign');
		session_start();

	}
	static private function setTimezone() {
		date_default_timezone_set('PRC');
	}

	static private function setHeader() {
		header('Content-type:text/html;charset=utf-8');
	}

	static private function setConstPath() {
		define('CORE_PATH', ROOT_PATH . '/core');
		define('CONF_PATH', CORE_PATH . '/conf');
		define('LIB_PATH', CORE_PATH . '/lib');
		define('PUBLIC_PATH', ROOT_PATH . '/public');
		define('CACHE_PATH', PUBLIC_PATH . '/cache');
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
		require LIB_PATH . '/function/common.php';

		spl_autoload_register(function ($className) {

			$path = '';

			if (strpos($className, 'Const') !== false) {
				$path = LIB_PATH . '/const/' . $className . '.php';

			} elseif (strpos($className, 'Base') > 0) {
				$path = ROOT_PATH . '/' . lcfirst(str_replace('Base', '', $className)) . '/' . $className . '.php';
			} else {
				$path = LIB_PATH . '/class/' . $className . '.class.php';
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