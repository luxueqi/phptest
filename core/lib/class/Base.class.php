<?php
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class Base {
	protected $assign = [];

	protected function view($path = __A__) {
		extract($this->assign);
		require './public/view/' . __M__ . '/' . $path . '.html';
	}

	protected function assign($name, $data) {
		$this->assign[$name] = $data;
	}

	protected function encookie($id, $un, $pwd, $tt) {

		return strrev(randStr(4, 2) . base64_encode($id . ':' . $tt . ':' . md5($pwd . C('wsign')['key'] . $un . $tt)) . randStr(4, 2));
	}

	protected function decookie($enstr, &$arr) {

		$arr = explode(':', base64_decode(substr(strrev($enstr), 4, strlen($enstr) - 8)));
		//var_dump($enstr, $arr);exit;
		if (count($arr) == 3) {
			return true;
		}

		return false;
	}
/**
 * [verifycookie description]
 * @param  [type] $arr [由cookie字符串(id:time:md5)解密出来的数组]
 * @param  [type] $un  [description]
 * @param  [type] $pwd [description]
 * @return [type]      [description]
 */
	protected function verifycookie($arr, $un, $pwd) {
		if (time() < $arr[1] && $arr[2] === md5($pwd . C('wsign')['key'] . $un . $arr[1])) {

			return true;
		}

		return false;
	}

	/**
	 * [checkParams description]
	 * @param  array  $param [key=>检查的参数，val=>使用的规则【email|noempty|phone|url|ip|regex|int】]
	 * @return array  $returnParam      [返回数组]
	 */
	protected function checkParams($param, $msg = []) {
		$flag = true;
		$returnParam = [];
		foreach ($param as $key => $value) {
			if ($value == 'int') {
				$flag = is_numeric(G($key)) && G($key) > 0;
			} elseif ($value == 'email') {
				$flag = Validate::R(G($key), Validate::VEMAIL);
			} elseif ($value == 'noempty') {
				$flag = !empty(G($key));
			} elseif ($value == 'phone') {
				$flag = Validate::R(G($key), Validate::VPHONE);
			} elseif ($value == 'url') {
				$flag = Validate::R(G($key), Validate::VURL);
			} elseif ($value == 'ip') {
				$flag = Validate::R(G($key), Validate::VIP);
			} elseif (strpos($value, 'regex:') === 0) {
				$flag = Validate::R(G($key), $value);
			}
			if (!$flag) {

				exitMsg(ErrorConst::API_PARAMS_ERRNO, isset($msg[$key]) ? $msg[$key] : $key . ' param error', [$key => G($key)]);
			}
			$returnParam[$key] = G($key);
		}
		return $returnParam;
	}
}

?>