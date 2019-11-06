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
		Session('lasttime', date('Y-m-d H:i:s', $info['lasttime']));
		Session('lastip', long2ip($info['lastip']));
		$this->db('login')->where('lasttime=' . time() . ',lastip=' . ip2long($_SERVER['REMOTE_ADDR']))->save($info['id']);
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
				$db = Db::getInstance();
				$info = $db->exec("select name,email,pwd,lastip,lasttime from login where id={$id}")->getOne();
				if ($this->verifycookie($arr, $info['email'], $info['pwd'])) {
					$info['id'] = $id;
					$this->setLoginInfo($info, $db);

					return true;
				}
			}
		}
		return false;
	}

	protected function slist($field, $table, $view) {
		$db = Db::getInstance();
		$arr = $db->exec("select $field from $table")->getAll();
		$this->assign('list', $arr);
		$this->assign('count', count($arr));
		$this->view($view);
	}

}

?>