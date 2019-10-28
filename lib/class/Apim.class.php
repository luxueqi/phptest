<?php

/**
 *
 */
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class Apim {

	public static function run() {
		if (isset($_SERVER['REDIRECT_SCRIPT_URL']) && stripos($_SERVER['REDIRECT_SCRIPT_URL'], '/api/') == 0) {

			$arr = explode('/', $_SERVER['REDIRECT_SCRIPT_URL'], 4);
			unset($arr[0]);
			$path = "./api/{$arr[2]}/Api.php";
			if (is_file($path)) {
				require "./api/{$arr[2]}/Api.php";
				if (method_exists('Api', $arr[3])) {
					(new Api)->$arr[3]();
					return;
				}

			}
		}
		exitMsg(ErrorConst::API_ERRNO, 'no');
	}
}

?>