<?php
if (!defined('EXITFORBID')) {
	exit('forbid');
}

class Core {

	public static function init() {
		self::setHeader();
		self::setTimezone();
		self::setDebug();
		self::setLoad();

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

			if (strpos($className, 'Const') !== false) {
				require ('./lib/const/' . $className . '.php');

			} else {
				require ('./lib/class/' . $className . '.class.php');
			}

		}, TRUE, TRUE);
	}

}

?>