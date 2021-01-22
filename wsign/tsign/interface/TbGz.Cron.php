<?php

if (!defined('EXITFORBID')) {
	exit('forbid');
}

class TbGz implements CronInterface {

	private $tieba;

	function sql() {
		return 'SELECT z.uid,z.cookie,z.tbs,g.fid,g.name,g.id,z.name un ,g.zid from tb_zh z INNER JOIN tb_gz g on g.zid=z.id and g.status=0 and z.status=1 order by z.order desc LIMIT 5';
	}

	function run($resi, &$condition, &$info) {

		if (!$this->tieba) {
			$this->tieba = new Tieba();
		}

		$rs = $this->tieba->sign($resi['cookie'], $resi['tbs'], $resi['fid'], $resi['uid'], $resi['name']);

		//dump($rs);exit;

		$condition = isset($rs['error_code']) && ($rs['error_code'] == '160002' || $rs['error_code'] == '0');

		if (!$condition) {
			$info = [$resi['zid'], '贴吧签到', $resi['un'], $resi['name'], $rs['error_msg']];
			//$info = [$resi['un'], '贴吧签到:' . $resi['name'], $rs['error_msg']];
		}

	}

	function end($idstr, $status) {

		Db::getInstance()->exec("update tb_gz set status={$status} where id in({$idstr})");

	}

}

?>