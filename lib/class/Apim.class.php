<?php

/**
 *
 */
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class Apim {

	public static function run($api_file, $method) {

		$path = "./api/$api_file/Api.php";
		if (is_file($path)) {
			require_once $path;

			if (method_exists('Api', $method)) {

				(new Api)->$method();
				return;
			}

		}

		exitMsg(ErrorConst::API_ERRNO, 'no api');
	}
}

?>