<?php
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class RunBase {
	public static function run($moudle, $api_file, $method) {

		$path = "./$moudle/$api_file/Api.php";
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