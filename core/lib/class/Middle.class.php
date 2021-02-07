<?php
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class Middle {

	public static function Mid($method, $rpath, $func, $fund = NULL) {

		$method = strtolower($method);

		$rpath = str_replace('/', '\\/', $rpath);

		if (preg_match('/^' . $rpath . '$/', '/' . __M__ . '/' . __C__ . '/' . __A__)) {
			if ($method == 'post|get' || $method == 'get|post' || isGetPostAjax($method)) {
				if (!is_callable($func)) {
					throw new Exception("{$func} is not function");

					//die('Middle::mid 请输入一个方法');
				}
				$func();
				return new Middle;
			}
		}
		if (is_callable($fund)) {
			$fund();
		}

		return new Middle;

	}

	public static function Last($func) {
		$func();
	}

}

?>