<?php

/**
 *
 */
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class Api extends WsignBase {

	public function __construct() {
		if ($this->checkLogin()) {

			header("location:/wsign-admin-index.html");
			exit();
		}
	}

	public function captcha() {

		creatCaptcha();
	}

	private function decodersa($endata) {
		$endata = pack('H*', $endata);
		if (openssl_private_decrypt($endata, $dedata, C('login')['rsa_private'], OPENSSL_NO_PADDING)) {
			//var_dump(strrev(trim($dedata)));exit;
			return strrev(substr(trim($dedata), 13));
		}

		return '';

	}

	public function callback() {

		$msg = '授权失败,正在跳转...';
		$url = '/wsign-login-login.html';
		if ($token = OuthMy::create('WbOuth')->getToken()) {
			//不能用uid做登陆状态，简单测试
			$res = $this->db('login')->filed('id,name')->where('wbuid=' . ($token['uid'] + 0))->getOne();

			if (!empty($res)) {
				$this->setLoginInfo($res);
				$msg = '授权成功,正在跳转...';
				$url = '/wsign-admin-index.html';

			} else {
				$msg = '绑定微博账号错误';

			}

		}

		$this->jump($url, $msg);

	}

	public function login() {
		//var_dump($this->db('login')->filed('email,pwd')->where('"yu",:idf', [':idf' => 'sdsd'])->save());exit;
		//Cookie('auth', base64_encode($res['id'] . ':' . $tt . ':' . md5($pwd . 'woshishui' . $un)), 86400 * 7);exit;
		if (isGetPostAjax('post')) {

			$params = $this->checkParams(['un' => 'email', 'pwd' => 'regex:^[0-9a-f]{256}$']);
			checkCaptcha(G('captcha'));
			$un = $params['un'];
			$pwd = $this->decodersa($params['pwd']);

			try {
				//$db = Db::getInstance();

				//$res = $db->exec("select id,name,lasttime,lastip from login where email=:un and pwd=:pwd", [':un' => $un, ':pwd' => $pwd])->getOne();

				$res = $this->db('login')->filed('id,name')->where('email=:un and pwd=:pwd', [':un' => $un, ':pwd' => $pwd])->getOne();
				//var_dump($res);exit;

				if (!empty($res)) {

					if (isset($_POST['online'])) {
						$this->wencookie($res['id'], $un, $pwd);
					}
					$this->setLoginInfo($res);
					//var_dump();exit;
					//$db->exec('update login set lasttime=' . time() . ',lastip=' . ip2long($_SERVER['REMOTE_ADDR']) . ' where id=' . $res['id']);
					exitMsg(ErrorConst::API_SUCCESS_ERRNO, '登陆成功');
				}
				exitMsg(2, '登陆失败,用户名或密码错误');
			} catch (PDOException $e) {
				exitMsg(ErrorConst::API_CATCH_ERRNO, 'fail');
			}

		}

		$this->assign('code_url', OuthMy::create('WbOuth')->getCodeUrl());

		$this->view('login');
	}

}

?>