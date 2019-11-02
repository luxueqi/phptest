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

	public function member() {
		$this->slist('id,name,weixin as wid,wuid', 'user', 'member-list');
	}
	public function madd() {
		if (isGetPostAjax('post')) {
			$cookie = G('cookie');
			$id = G('id') + 0;
			$wuid = G('wuid') + 0;
			$wb = new Weibo($cookie);
			$uid = '';
			try {
				$uidname = $wb->getUidName();

				if (isset($uidname['id']) && $wuid == $uidname['id']) {
					//var_dump('update user set cookie="' . $cookie . '",name="' . $uidname['name'] . '" where id=' . $id);exit;
					Db::getInstance()->exec('update user set cookie=:cookie,name=:name where id=:id', [':id' => $id, ':cookie' => $cookie, ':name' => $uidname['name']]);
					exitMsg(1, '修改成功');

				}
				exitMsg(2, '修改失败,修改用户和提交用户不匹配');
			} catch (PDOException $ee) {
				exitMsg(ErrorConst::API_CATCH_REENO, 'fail');
			} catch (Exception $e) {
				exitMsg($e->getCode(), $e->getMessage());
			}
		}
		$this->view('member-add');
	}

	public function mdel() {
		$this->comdel('user');

	}

	public function mlist() {
		$this->slist('*', 'wcount', 'mlist');

	}
	public function mgadd() {
		if (isGetPostAjax('post')) {
			$id = G('id') + 0;
			$count = G('count') + 0;
			try {
				Db::getInstance()->exec('update wcount set count=:count where id=:id', [':id' => $id, ':count' => $count]);
				exitMsg(1, '修改成功');
			} catch (PDOException $e) {
				exitMsg(ErrorConst::API_CATCH_REENO, 'fail');
			}
		}
		$this->view('m-add');
	}

	public function gdel() {
		$this->comdel('wcount');
	}

	private function comdel($table) {
		$id = G('id') + 0;

		try {
			Db::getInstance()->exec('delete from ' . $table . ' where id=' . $id);
			exitMsg(1, '删除成功');
		} catch (PDOException $e) {
			exitMsg(2, 'fail');
		}
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