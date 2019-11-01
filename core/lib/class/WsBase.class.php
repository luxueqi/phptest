<?php
if (!defined('EXITFORBID')) {
	exit('forbid');
}
define('WSIGN_VIEW_PATH', ROOT_PATH . '/public/view/wsign');
class WsBase {

	protected $assign = [];

	protected function view($path) {
		extract($this->assign);
		require './public/view/wsign/' . $path . '.html';
	}

	protected function assign($name, $data) {
		$this->assign[$name] = $data;
	}

	protected function checkLogin() {
		if (Session('name') != false) {

			return true;

		}
		return $this->decookie();
	}

	protected function setLoginInfo($info, &$db) {
		Session('name', $info['name']);
		Session('uid', $info['id']);
		Session('lasttime', date('Y-m-d H:i:s', $info['lasttime']));
		Session('lastip', long2ip($info['lastip']));
		$db->exec('update login set lasttime=' . time() . ',lastip=' . ip2long($_SERVER['REMOTE_ADDR']) . ' where id=' . $info['id']);
	}

	protected function encookie($id, $un, $pwd) {
		$tt = time() + 86400 * 7;
		return setcookie('auth', randStr(3, 2) . base64_encode($id . ':' . $tt . ':' . md5($pwd . 'woshishui' . $un)) . randStr(3, 2), $tt, '/', "", false, true);
	}

	protected function decookie() {
		if (isset($_COOKIE['auth'])) {
			$arr = explode(':', base64_decode(substr($_COOKIE['auth'], 3, strlen($_COOKIE['auth']) - 6)));
			if (count($arr) == 3) {
				$id = $arr[0] + 0;
				$db = Db::getInstance();
				$info = $db->exec("select name,email,pwd,lastip,lasttime from login where id={$id}")->getOne();
				//var_dump($arr, $info);exit();
				if (time() < $arr[1] && $arr[2] === md5($info['pwd'] . 'woshishui' . $info['email'])) {
					$info['id'] = $id;
					$this->setLoginInfo($info, $db);

					return true;
				}
			}
		}
		return false;
	}

}

?>