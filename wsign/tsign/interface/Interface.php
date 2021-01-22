<?php

if (!defined('EXITFORBID')) {
	exit('forbid');
}

interface CronInterface {

	public function sql();
	public function run($resi, &$condition, &$info);
	public function end($idstr, $status);
}

?>