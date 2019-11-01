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

			header("location:/wsign/admin/index");
			exit();
		}
	}

	public function login() {
		//Cookie('auth', base64_encode($res['id'] . ':' . $tt . ':' . md5($pwd . 'woshishui' . $un)), 86400 * 7);exit;
		if (isGetPostAjax('post')) {
			$un = G('un');
			$pwd = G('pwd');
			//var_dump($_POST);exit;
			if (!Validate::R($un, Validate::VEMAIL) || !Validate::R($pwd, 'regex:^[\S]{6,12}$')) {
				exitMsg(ErrorConst::API_PARAMS_ERRNO, '参数错误');
			}
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
				exitMsg(ErrorConst::API_CATCH_REENO, $e->getMessage());
			}

		}

		$this->view('login');
	}

}

?>