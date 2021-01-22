<?php

if (!defined('EXITFORBID')) {
	exit('forbid');
}

class WkSign implements CronInterface {
	function sql() {
		return 'SELECT s.id,s.uid,z.cookie,z.name from wk_sign s INNER JOIN tb_zh z on s.uid=z.id and s.status=0  LIMIT 1';
	}

	function run($resi, &$condition, &$info) {

		try {

			$info = [$resi['uid'], '文库签到', $resi['name'], '', ''];

			//$info = [$resi['name'], '文库签到', ''];

			$rs = WenKu::sign('BDUSS=' . $resi['cookie']);

			//dump($rs);

			$condition = isset($rs['errno']) && $rs['errno'] == 0;

			if (!$condition) {
				$info[4] = $rs['errno'] . '-' . $rs['errmsg'];
			}
		} catch (Exception $e) {
			$info[4] = $e->getMessage();
		}

	}

	function end($idstr, $status) {

		Db::getInstance()->exec("update wk_sign set status={$status} where id in({$idstr})");

	}
}

?>