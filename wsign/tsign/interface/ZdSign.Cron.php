<?php

if (!defined('EXITFORBID')) {
	exit('forbid');
}

class ZdSign implements CronInterface {

	function sql() {
		return 'SELECT s.id,s.uid,s.stoken,z.cookie,z.name from zd_sign s INNER JOIN tb_zh z on s.uid=z.id and s.status=0  LIMIT 1';
	}

	function run($resi, &$condition, &$info) {

		try {
			$stoken = $resi['stoken'];
			$info = [$resi['uid'], '知道签到', $resi['name'], '', ''];
			//$info = [$resi['name'], '知道签到', ''];
			if (empty($stoken)) {
				$stoken = Zhidao::getStoken('BDUSS=' . $resi['cookie']);
				if (!empty($stoken)) {
					Db::getInstance()->exec("update zd_sign set stoken='{$stoken}' where id={$resi['id']}");
				}
			}

			$rs = Zhidao::sign('BDUSS=' . $resi['cookie'], $stoken);

			//dump($rs);

			$condition = isset($rs['errorNo']) && ($rs['errorNo'] == 0 || $rs['errorNo'] == 2);

			if (!$condition) {
				$info[4] = $rs['errorNo'] . '-' . $rs['errorMsg'];
			}
		} catch (Exception $e) {
			$info[4] = $e->getMessage();
		}
	}

	function end($idstr, $status) {

		Db::getInstance()->exec("update zd_sign set status={$status} where id in({$idstr})");

	}
}

?>