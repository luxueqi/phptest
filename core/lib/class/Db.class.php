<?php
if (!defined('EXITFORBID')) {
	exit('forbid');
}

class Db {

	private static $instance = null;

	private $smt;

	private $db;

	private $table;

	private $where = '';

	private $preparedata = [];

	private $colfield = '*';

	private $isgetSql = false;

	private static $conf;

	private $islock = false;

	private function __clone() {}

	public static function getInstance($conf = []) {
		self::$conf = $conf;
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		if (self::$instance->db) {

			return self::$instance;
		}
		//return false;
	}

	private function __construct() {

		//echo __CLASS__;exit();

		if (empty(self::$conf) && function_exists('C')) {
			self::$conf = C('db');
		}

		$this->db = new PDO(self::$conf['DSN'], self::$conf['username'], self::$conf['password']);
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

	}
	/**
	 * [exec description]
	 * @param  [type] $sql  [占位符形式 select name from test where id=:id]
	 * @param  array  $data [查询的条件[':id'=>1]]
	 * @return [type]       [description]
	 */
	public function exec($sql, $data = []) {
		try {
			$sql = trim($sql);

			if ($this->islock) {
				$sql .= ' for update';
			} else {

				$sql = str_replace(' for update', '', $sql);
			}
			//var_dump($sql);exit;
			$this->echoSql($sql);
			$this->smt = $this->db->prepare($sql);

			if (empty($data)) {
				$this->smt->execute();
			} else {
				$this->smt->execute($data);
			}
		} catch (PDOException $e) {
			throw new PDOException("sql err:[{$sql}]:" . $e->getMessage());

		}

		return $this;

	}

	public function beginTransaction() {

		$this->db->beginTransaction();

		return $this;

	}

	public function lock($islock = false) {
		$this->islock = $islock;
		return $this;
	}

	public function commit() {

		$lastid = $this->getLastId();

		$this->db->commit();

		return $lastid;
	}

	public function rollback() {
		return $this->db->rollback();

		//return $this;
	}

	public static function config($conf) {
		self::$conf = $conf;
	}

	public static function table($table) {
		$db = self::getInstance(self::$conf);
		$db->table = $table;
		$db->isgetSql = false;
		return $db;
	}

	private function isprepareParam($v) {

		return is_string($v) && $v != '?' && strpos($v, ':') !== 0 ? "'$v'" : $v;
	}

	public function where($where, $data = []) {

		if (is_array($where)) {
			$condition = 'and';
			foreach ($where as $key => $value) {
				if (is_array($value)) {
					$condition = $key;
					foreach ($value as $k => $v) {

						$v = $this->isprepareParam($v);
						$this->where .= "{$k}={$v} {$key} ";
					}
					//$this->where = rtrim($this->where);
					//$this->where = substr($this->where, 0, strlen($this->where) - strlen($key));

				} else {

					$value = $this->isprepareParam($value);
					$this->where .= "{$key}={$value} {$condition} ";

				}

			}
			$this->where = rtrim($this->where);
			$this->where = "where " . substr($this->where, 0, strlen($this->where) - strlen($condition));

		} else {
			$this->where = "where " . $where;
		}

		$this->preparedata = $data;

		return $this;
	}

	public function filed($col = '*') {
		if (is_array($col)) {
			$this->colfield = '';
			foreach ($col as $value) {
				$this->colfield .= $value . ',';
			}
			$this->colfield = rtrim($this->colfield, ',');
		} else {
			$this->colfield = $col;
		}

		return $this;
	}

	public function select($id = '') {
		if (is_int($id) && $id > 0) {
			$this->where('id=?', [$id]);
		}

		return $this->exec("select {$this->colfield} from {$this->table} {$this->where}", $this->preparedata)->getAll();
	}

	public function find($id = '') {
		if (is_int($id) && $id > 0) {
			$this->where('id=?', [$id]);
		}
		return $this->exec("select {$this->colfield} from {$this->table} {$this->where}", $this->preparedata)->getOne();
	}
/**
 * [insert description]
 * @param  array $insertdata  [要插入的数据，多数据插入格式为二维数组]
 * @param  array  $preparedata [description]
 * @return [type]              [description]
 */
	public function insert($insertdata, $preparedata = []) {

		if (empty($this->colfield) || $this->colfield == '*') {
			throw new Exception("未知列");
		}

		$sql = '';

		$more = false;

		foreach ($insertdata as $value) {
			if (is_array($value)) {
				$more = true;
				$sql .= '(';
				foreach ($value as $v) {
					$v = $this->isprepareParam($v);
					$sql .= "{$v},";
				}
				$sql = rtrim($sql, ',') . '),';
			} else {
				$value = $this->isprepareParam($value);
				$sql .= "{$value},";
			}
		}
		$sql = rtrim($sql, ',');
		$sql = $more ? $sql : "({$sql})";

		return $this->exec("insert into {$this->table}({$this->colfield})values{$sql}", $preparedata)->getLastId();

	}
/**
 * [update description]
 * @param  array  $udata       [['列'=>'值']]
 * @param  array  $preparedata [使用预处理的数据]
 * @return int              影响的行数
 */
	public function update($udata, $preparedata = []) {
		$sql = "update {$this->table} set ";

		foreach ($udata as $key => $value) {
			$value = $this->isprepareParam($value);
			$sql .= "{$key}={$value},";
		}
		$sql = rtrim($sql, ',') . " {$this->where}";
		$this->preparedata = array_merge($preparedata, $this->preparedata);
		return $this->exec($sql, $this->preparedata)->rowCount();

	}

	public function delete($id = []) {
		$sql = "delete from {$this->table}";
		if (is_int($id) && $id > 0) {
			$this->where('id=?', [$id]);
		}
		if (empty($this->where)) {
			die('警告,你没有设置删除条件');
		}
		$sql .= " {$this->where}";
		return $this->exec($sql, $this->preparedata)->rowCount();
	}

	public function getSql() {
		$this->isgetSql = true;
		return $this;
	}

	private function echoSql($sql) {
		if ($this->isgetSql) {
			die($sql);
		}
	}

	public function getOne() {

		return $this->smt->fetch(PDO::FETCH_ASSOC);

	}

	public function getAll() {
		return $this->smt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function rowCount() {
		return $this->smt->rowCount();
	}

	public function getLastId() {
		return $this->db->lastInsertId();
	}

}

?>