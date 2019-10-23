<?php

class Db {

	private static $instance = null;

	private $smt;

	private $db;

	private function __clone() {}

	public static function getInstance() {

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

		$confAll = C('db');

		$this->db = new PDO($confAll['DSN'], $confAll['username'], $confAll['password']);
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

	}

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