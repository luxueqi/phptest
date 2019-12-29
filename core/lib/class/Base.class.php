<?php
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class Base {
	protected $assign = [];

	protected $table;
//select * from w where id=
	//delect from w where id=
	//insert into w()values()
	//update w set c=1,t=2 where id=
	protected $dbfiled = '*';

	protected $dbdata = [];

	protected $dbwhere;

	protected $cachet = []; //['key'=>[],'key1']

	//protected $cachefalg = false;
	//
	public function __construct() {

		$this->cacheitem(['time' => 144000, 'qflag' => G('qflag', false)]);

	}

	private function boolcache(&$conf = []) {

		if (isset(C(__M__)['cache']) ? C(__M__)['cache'] : false) {

			foreach ($this->cachet as $key => $value) {
				if (is_array($value)) {
					if (__A__ == $key) {
						$conf = array_merge($conf, $value);
						return true;
					}
				} elseif (__A__ == $value) {

					return true;

				}
			}

		}
		return false;
	}

	private function cacheitem($conf = ['time' => 72000, 'qflag' => false]) {

		/*if (C(__M__)['cache'] && in_array(__A__, $this->cachet)) {

		Cache::read($conf);
		}*/

		if ($this->boolcache($conf)) {
			//var_dump($conf);exit;
			Cache::read($conf);
		}
	}

	public function __destruct() {
		/*if (C(__M__)['cache'] && in_array(__A__, $this->cachet)) {
		Cache::write();
		}*/
		//echo "string";
		if ($this->boolcache()) {

			Cache::write();
		}

	}

	protected function view($path = __A__) {
		extract($this->assign);
		require './public/view/' . __M__ . '/' . $path . '.html';
	}

	protected function assign($name, $data) {
		$this->assign[$name] = $data;
	}

	protected function encookie($id, $un, $pwd, $tt, $key = '') {
		$key = $key == '' ? C('base')['key'] : $key;
		return strrev(randStr(4, 2) . base64_encode($id . ':' . $tt . ':' . md5($pwd . $key . $un . $tt)) . randStr(4, 2));
	}

	protected function decookie($enstr, &$arr) {

		$arr = explode(':', base64_decode(substr(strrev($enstr), 4, strlen($enstr) - 8)));
		//var_dump($enstr, $arr);exit;
		if (count($arr) == 3) {
			return true;
		}

		return false;
	}
/**
 * [verifycookie description]
 * @param  [type] $arr [由cookie字符串(id:time:md5)解密出来的数组]
 * @param  [type] $un  [description]
 * @param  [type] $pwd [description]
 * @return [type]      [description]
 */
	protected function verifycookie($arr, $un, $pwd, $key = '') {
		$key = $key == '' ? C('base')['key'] : $key;
		if (time() < $arr[1] && $arr[2] === md5($pwd . $key . $un . $arr[1])) {

			return true;
		}

		return false;
	}

	/**
	 * [checkParams description]
	 * @param  array  $param [key=>检查的参数，val=>使用的规则【email|noempty|phone|url|ip|regex|int】]
	 * @return array  $returnParam      [返回数组]
	 */
	protected function checkParams($param, $msg = []) {
		$flag = true;
		$returnParam = [];
		foreach ($param as $key => $value) {
			if ($value == 'int') {

				$flag = is_numeric(G($key)) && G($key) > 0;
			} elseif ($value == 'email') {
				$flag = Validate::R(G($key), Validate::VEMAIL);
			} elseif ($value == 'noempty') {
				$flag = !empty(G($key));
			} elseif ($value == 'phone') {
				$flag = Validate::R(G($key), Validate::VPHONE);
			} elseif ($value == 'url') {
				$flag = Validate::R(G($key), Validate::VURL);
			} elseif ($value == 'ip') {
				$flag = Validate::R(G($key), Validate::VIP);
			} elseif (strpos($value, 'regex:') === 0) {
				$flag = Validate::R(G($key), $value);
			}
			if (!$flag) {

				exitMsg(ErrorConst::API_PARAMS_ERRNO, isset($msg[$key]) ? $msg[$key] : $key . ' param error', [$key => G($key)]);
			}
			$returnParam[$key] = G($key);
		}
		return $returnParam;
	}

	protected function jump($url, $msg, $time = 2) {

		header("Refresh:{$time},Url={$url}");
		exit($msg);

	}

	protected function db($table) {
		$this->dbfiled = '*';

		$this->dbdata = [];

		$this->dbwhere = '';
		$this->table = $table;
		return $this;
	}
/**
 * [where description]
 * @param  [type] $s    [形如c1=r1,c2=r2,update;
 *                      	c1=r2&&c2=r2,select delete;
 *                      	c1,c2,c3,insert;
 *                      ]
 * @param  array  $data [description]
 * @return [type]       [description]
 */
	protected function where($s, $data = []) {
		$this->dbwhere = 'where ' . $s;
		$this->dbdata = $data;
		return $this;
	}

	protected function filed($v = '*') {
		$this->dbfiled = $v;
		return $this;
	}

	protected function select() {
		$sql = "select {$this->dbfiled} from {$this->table} {$this->dbwhere}";

		return Db::getInstance()->exec($sql, $this->dbdata)->getAll();
	}

	protected function getOne() {
		$sql = "select {$this->dbfiled} from {$this->table} {$this->dbwhere}";
		//var_dump($sql, $this->dbdata);exit;
		return Db::getInstance()->exec($sql, $this->dbdata)->getOne();
	}

	protected function delete($id = '') {

		if ($id == '' && $this->dbwhere == '') {
			die('安全问题');
		}
		if ($id != '') {
			$this->where('id=:id', [':id' => $id + 0]);
		}
		$sql = "delete from {$this->table} {$this->dbwhere}";

		//($sql, $this->dbdata);exit;

		return Db::getInstance()->exec($sql, $this->dbdata)->rowCount();
	}

/**
 * [save description]
 * @param  string $id [ID=‘’insert操作，否则update操作]
 * @return [type]     [description]
 */
	protected function save($id = '') {
		$sql = '';
		$this->dbwhere = str_replace('where ', '', $this->dbwhere);

		if ($id == '') {

			$this->dbfiled = $this->dbfiled == '*' ? ' ' : "({$this->dbfiled})";
			$sql = "insert into {$this->table}{$this->dbfiled} values{$this->dbwhere}";
			//var_dump($sql);exit;
		} else {
			//$this->dbwhere = str_replace('&', ',', $this->dbwhere);
			//$this->dbwhere = str_replace('and', ',', $this->dbwhere);
			$sql = "update {$this->table} set {$this->dbwhere} where id=:id";
			$this->dbdata[':id'] = $id + 0;
		}
		//die($sql);
		return Db::getInstance()->exec($sql, $this->dbdata)->rowCount();
	}
}

?>