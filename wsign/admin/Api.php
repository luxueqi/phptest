<?php
/**
 *
 */
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class Api extends WsBase {

	public function __construct() {
		if (!$this->checkLogin()) {

			header("location:/wsign-login-login.html");
			exit();
		}

	}

	public function index() {
		$this->view('index');
	}

	public function welcome() {

		$this->view('welcome');
	}

	public function logout() {
		Session('name', null);
		Cookie('auth', null, -1);
		echo "<script>alert('退出成功');location.href='/wsign-login-login.html';</script>";
		//header("location: /wsign/login/login");
		exit;
	}
}

?>