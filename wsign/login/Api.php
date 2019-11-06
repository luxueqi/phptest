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

	public function login() {
		//var_dump($this->db('login')->filed('email,pwd')->where('"yu",:idf', [':idf' => 'sdsd'])->save());exit;
		//Cookie('auth', base64_encode($res['id'] . ':' . $tt . ':' . md5($pwd . 'woshishui' . $un)), 86400 * 7);exit;
		if (isGetPostAjax('post')) {

			$params = $this->checkParams(['un' => 'email', 'pwd' => 'regex:^[\S]{6,12}$']);

			$un = $params['un'];
			$pwd = $params['pwd'];

			checkCaptcha(G('captcha'));

			try {
				//$db = Db::getInstance();

				//$res = $db->exec("select id,name,lasttime,lastip from login where email=:un and pwd=:pwd", [':un' => $un, ':pwd' => $pwd])->getOne();

				$res = $this->db('login')->filed('id,name,lasttime,lastip')->where('email=:un and pwd=:pwd', [':un' => $un, ':pwd' => $pwd])->getOne();
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

		$this->view('login');
	}

}

?>