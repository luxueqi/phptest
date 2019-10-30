<?php

/**
 *
 */
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class Api {

	function __construct() {

	}

	public function login() {
		$un = G('un');

		$pwd = G('pwd');

		if (empty($un) || empty($pwd)) {
			exitMsg(ErrorConst::API_PARAMS_ERRNO, '输入用户名或者密码');
		}

		try {
			$weibo = new Weibo();
			$res = $weibo->login($un, $pwd, $cookie);
			if (!empty($cookie)) {
				exitMsg(10000, 'ok', ['cookie' => $cookie]);

			}
			echo $res;
		} catch (Exception $e) {
			exitMsg(ErrorConst::API_CATCH_REENO, $e->getMessage());
		}
	}

	public function block() {
		if (G('ruid', 0) === 0 || empty(G('huati')) || empty(G('cookie'))) {
			exitMsg(ErrorConst::API_PARAMS_ERRNO, '参数错误');
		}

		try {
			$wb = new Weibo(G('cookie'));
			echo $wb->block(G('ruid'), G('huati'));

		} catch (Exception $e) {
			exitMsg(ErrorConst::API_CATCH_REENO, $e->getMessage());
		}

	}
}

?>