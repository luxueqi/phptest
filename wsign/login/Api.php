<?php

/**
 *
 */
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class Api extends WsBase {

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
		//Cookie('auth', base64_encode($res['id'] . ':' . $tt . ':' . md5($pwd . 'woshishui' . $un)), 86400 * 7);exit;
		if (isGetPostAjax('post')) {

			$params=$this->checkParams(['un'=>'email','pwd'=>'regex:^[\S]{6,12}$']);

			$un = $params['un'];
			$pwd =$params['pwd']
			
			checkCaptcha(G('captcha'));
			
			try {
				$db = Db::getInstance();

				$res = $db->exec("select id,name,lasttime,lastip from login where email=:un and pwd=:pwd", [':un' => $un, ':pwd' => $pwd])->getOne();

				if (!empty($res)) {

					if (isset($_POST['online'])) {
						$this->encookie($res['id'], $un, $pwd);
					}
					$this->setLoginInfo($res, $db);
					//var_dump();exit;
					//$db->exec('update login set lasttime=' . time() . ',lastip=' . ip2long($_SERVER['REMOTE_ADDR']) . ' where id=' . $res['id']);
					exitMsg(1, '登陆成功');
				}
				exitMsg(2, '登陆失败,用户名或密码错误');
			} catch (PDOException $e) {
				exitMsg(ErrorConst::API_CATCH_REENO, 'fail');
			}

		}

		$this->view('login');
	}

}

?>