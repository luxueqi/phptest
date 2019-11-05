<?php

/**
 *
 */
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class Api extends Base {

	public function login() {

		$params = $this->checkParams(['un' => 'noempty', 'pwd' => 'noempty']);
		try {
			$weibo = new Weibo();
			$res = $weibo->login($params['un'], $params['pwd'], $cookie);
			if (!empty($cookie)) {
				exitMsg(10000, 'ok', ['cookie' => $cookie]);

			}
			echo $res;
		} catch (Exception $e) {
			exitMsg(ErrorConst::API_CATCH_ERRNO, $e->getMessage());
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
			exitMsg(ErrorConst::API_CATCH_ERRNO, $e->getMessage());
		}

	}
}

?>