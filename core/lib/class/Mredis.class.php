<?php

if (!defined('EXITFORBID')) {
	exit('forbid');
}

class Mredis {

	private static $instance;

	private static $conf;

	private $redis;

	private function __clone() {}

	private function __construct() {

		$this->redis = new Redis();

		$this->redis->connect(self::$conf['host'], self::$conf['port'], 10) or die('connect error');

		if (isset(self::$conf['pwd'])) {
			$this->redis->auth(self::$conf['pwd']) or die('auth error');
		}

	}

	public static function getInstance($conf = []) {
		if (empty($conf) && function_exists('C')) {
			$conf = C('redis');
		}

		self::$conf = $conf;

		if (is_null(self::$instance)) {

			self::$instance = new self();

		}

		return self::$instance;
	}

	public function addVal($key) {
		return $this->redis->incr($key);
	}

	public function exists($key) {
		return $this->redis->exists($key);
	}

	public function setVal($key, $va, $timeout = 0) {
		if ($timeout != 0) {
			return $this->redis->setex($key, $timeout, $va);
		}
		return $this->redis->set($key, $va);

	}

	public function getVal($key) {
		return $this->redis->get($key);
	}

	public function __destruct() {
		// TODO: Implement __destruct() method.
		$this->redis->close();
	}

}

?>