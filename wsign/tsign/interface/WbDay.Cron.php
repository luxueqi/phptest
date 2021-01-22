<?php

if (!defined('EXITFORBID')) {
	exit('forbid');
}

class WbDay implements CronInterface {
	function sql() {
		return 'SELECT w.id,w.uid,u.cookie,u.name from wb_day w INNER JOIN user u on w.uid=u.id and w.status=0  LIMIT 1';
	}

	function run($resi, &$condition, &$info) {
		try {

			$info = [$resi['uid'], '每日一善', $resi['name'], '', ''];
			//$info = [$resi['name'], '每日一善', ''];
			$rs = (new Weibo())->dayGy($resi['cookie']);
			$condition = true;

			foreach ($rs as $value) {
				if ($value['code'] != '100000') {
					$condition = false;

					$info[4] = addslashes(json_encode($rs));
					break;
				}
			}
		} catch (Exception $e) {
			$condition = false;
			$info[4] = $e->getMessage();

		}

	}

	function end($idstr, $status) {

		Db::getInstance()->exec("update wb_day set status={$status} where id in({$idstr})");

	}

}

?>