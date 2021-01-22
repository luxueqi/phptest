<?php
/**
 * Created by PhpStorm.
 * User: xx
 * Date: 2019/10/26
 * Time: 下午10:17
 */
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class Validate {
	const VEMAIL = 0;
	const VIP = 1;
	const VURL = 2;
	const VPHONE = 3;
	// const VREGEXP='';
	private static $rules = [FILTER_VALIDATE_EMAIL, FILTER_VALIDATE_IP, FILTER_VALIDATE_URL];
	/**
	 * [R description]
	 * @param [string] $data [检查的数据]
	 * @param [type] $r    [检查类型取值范围0-3 or regex:par ]
	 */
	public static function R($data, $r) {
		if (!in_array($r, [0, 1, 2, 3], true) && !preg_match('/^regex:(.+)$/', $r, $match)) {
			throw new Exception('规则ID错误', ErrorConst::VALIDATE_ERRNO);
		}

		if (in_array($r, [0, 1, 2], true)) {
			if (filter_var($data, self::$rules[$r]) !== false) {
				return true;
			}
			return false;
		} else {

			$par = $r == 3 ? '/^1(3|5|6|7|8|9)\d{9}$/' : '/' . $match[1] . '/';

			//var_dump($data, $par);
			return self::Regexc($data, $par);
		}

	}

	private static function Regexc($data, $par) {

		return preg_match($par, $data) == 1 ? true : false;
	}

}