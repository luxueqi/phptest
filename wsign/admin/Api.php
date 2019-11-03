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
			if (isGetPostAjax('post')) {
				exitMsg(-1, 'no login');
			}
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
		$this->slist('id,name,weixin as wid,wuid,status', 'user', 'member-list');
	}
	public function madd() {
		if (isGetPostAjax('post')) {
			$cookie = G('cookie');

			$params = $this->checkParams(['id' => 'int', 'wuid' => 'int', 'status' => 'regex:^[01]$']);

			try {
				if ($cookie != '') {
					$wb = new Weibo($cookie);
					$uidname = $wb->getUidName();

					if (isset($uidname['id']) && $params['wuid'] == $uidname['id']) {
						//var_dump('update user set cookie="' . $cookie . '",name="' . $uidname['name'] . '" where id=' . $id);exit;
						Db::getInstance()->exec('update user set cookie=:cookie,name=:name,status=:status where id=:id', [':id' => $params['id'], ':cookie' => $cookie, ':name' => $uidname['name'], ':status' => $params['status']]);
						exitMsg(1, '修改成功');

					}
				} else {
					Db::getInstance()->exec('update user set status=:status where id=:id', [':id' => $params['id'], ':status' => $params['status']]);
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

			$params = $this->checkParams(['id' => 'int', 'count' => 'int']);
			try {
				Db::getInstance()->exec('update wcount set count=:count where id=:id', [':id' => $params['id'], ':count' => $params['count']]);
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

		$params = $this->checkParams(['id' => 'int']);
		$id = $params['id'];
		try {
			if (Db::getInstance()->exec('delete from ' . $table . ' where id=' . $id)->rowCount() === 1) {
				exitMsg(1, '删除成功');
			}
			exitMsg(ErrorConst::API_PARAMS_ERRNO, '删除失败,请检查参数是否正确');
		} catch (PDOException $e) {
			exitMsg(ErrorConst::API_CATCH_REENO, 'fail');
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