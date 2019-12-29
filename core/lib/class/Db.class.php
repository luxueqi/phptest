<?php
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class Db {

	private static $instance = null;

	private $smt;

	private $db;

	private static $conf;

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

		if (empty(self::$conf)) {
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

		$this->smt = $this->db->prepare($sql);

		if (empty($data)) {
			$this->smt->execute();
		} else {
			$this->smt->execute($data);
		}

		return $this;

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

}

?>