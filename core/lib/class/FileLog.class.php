<?php
if (!defined('EXITFORBID')) {
	exit('forbid');
}

class FileLog {

	public static function Log($filename, $dir, $msg) {

		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}

		$cur_dir = $dir . '/' . date('Ymd');

		if (!is_dir($cur_dir)) {
			mkdir($cur_dir, 0777, true);
		}

		$msg = '[' . date('Y-m-d H:i:s') . '] ' . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '') . '--' . $msg . PHP_EOL;

		file_put_contents($cur_dir . '/' . $filename, $msg, FILE_APPEND);

	}
}

?>