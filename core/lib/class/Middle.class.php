<?php
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class Middle {

	public static function Mid($method, $rpath, $func) {

		$method = strtolower($method);

		$rpath = str_replace('/', '\\/', $rpath);

		if (preg_match('/^' . $rpath . '$/', '/' . __M__ . '/' . __C__ . '/' . __A__)) {
			if ($method == 'post|get' || $method == 'get|post' || isGetPostAjax($method)) {
				$func();
				return new Middle;
			}
		}

		return new Middle;

	}

}

?>