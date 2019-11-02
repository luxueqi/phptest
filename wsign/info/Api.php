<?php

class Api extends WsBase {
	public function __construct() {
		if (!$this->checkLogin()) {

			header("location:/wsign-login-login.html");
			exit();
		}

	}

	public function info() {
		$this->slist('id,uid,t_name as name,status', 'wgz', 'info');
	}

	public function einfo() {
		$this->slist('id,name,t_name as tname,errinfo info,time', 'werrinfo', 'einfo');
	}

}

?>