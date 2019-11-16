<?php
if (!defined('EXITFORBID')) {
	exit('forbid');
}
define('WSIGN_VIEW_PATH', ROOT_PATH . '/public/view/wsign');
class WsignBase extends Base {

	protected function checkLogin() {
		if (Session('name') != false) {

			return true;

		}
		return $this->wdecookie();
	}
	protected function needLogin($redricturl) {
		if (!$this->checkLogin()) {
			if (isGetPostAjax('post')) {
				exitMsg(-1, 'no login');
			}
			header("location:{$redricturl}");
			exit();
		}
	}
	protected function setLoginInfo($info) {
		Session('name', $info['name']);
		Session('uid', $info['id']);
		$time = time();
		$ip = ip2long($_SERVER['REMOTE_ADDR']);
		$this->db('login_info')->filed('uid,time,ip')->where("({$info['id']},$time,$ip)")->save();
		//Session('lasttime', date('Y-m-d H:i:s', $info['lasttime']));
		//Session('lastip', long2ip($info['lastip']));
		//$this->db('login')->where('time=' . time() . ',ip=' . ip2long($_SERVER['REMOTE_ADDR']))->save($info['id']);
		//var_dump($this->db('login')->where('lasttime=1,lastip=2,cc=:ii', [':ii' => 'as'])->save($info['id']));exit;
		//$db->exec('update login set lasttime=' . time() . ',lastip=' . ip2long($_SERVER['REMOTE_ADDR']) . ' where id=' . $info['id']);
	}

	protected function wencookie($id, $un, $pwd) {
		$tt = time() + 86400 * 7;
		return setcookie('auth', $this->encookie($id, $un, $pwd, $tt), $tt, '/', "", false, true);
	}

	protected function wdecookie() {
		if (isset($_COOKIE['auth'])) {

			if ($this->decookie($_COOKIE['auth'], $arr)) {
				$id = $arr[0] + 0;
				$info = $this->db('login')->filed('name,email,pwd')->where('id=' . $id)->getOne();
				//$db = Db::getInstance();
				//$info = $db->exec("select name,email,pwd,lastip,lasttime from login where id={$id}")->getOne();
				if ($this->verifycookie($arr, $info['email'], $info['pwd'])) {
					$info['id'] = $id;
					$this->setLoginInfo($info);

					return true;
				}
			}
		}
		return false;
	}
	protected function comdel($table) {

		$params = $this->checkParams(['id' => 'int']);
		$id = $params['id'];
		try {
			if (Db::getInstance()->exec('delete from ' . $table . ' where id=' . $id)->rowCount() === 1) {
				exitMsg(ErrorConst::API_SUCCESS_ERRNO, '删除成功');
			}
			exitMsg(ErrorConst::API_PARAMS_ERRNO, '删除失败,请检查参数是否正确');
		} catch (PDOException $e) {
			exitMsg(ErrorConst::API_CATCH_ERRNO, 'fail');
		}
	}

	protected function slist($field, $table, $view) {
		$db = Db::getInstance();
		//exit("select $field from $table");
		$arr = $db->exec("select $field from $table")->getAll();
		$this->assign('list', $arr);
		$this->assign('count', count($arr));
		$this->view($view);
	}

	protected function statuscomm($table) {
		$param = $this->checkParams(['id' => 'int', 'status' => 'regex:^[012]$']);

		try {
			$this->db($table)->where('status=:status', [':status' => !$param['status'] + 0])->save($param['id']);
			exitMsg(ErrorConst::API_SUCCESS_ERRNO, '更改成功');

		} catch (PDOException $e) {
			exitMsg(ErrorConst::API_CATCH_ERRNO, 'fail');
		}
	}

}

?>