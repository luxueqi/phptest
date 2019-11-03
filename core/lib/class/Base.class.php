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

	/**
	 * [checkParams description]
	 * @param  array  $param [key=>检查的参数，val=>使用的规则【email|phone|url|ip|regex|int】]
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