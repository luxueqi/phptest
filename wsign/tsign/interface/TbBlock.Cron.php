<?php

if (!defined('EXITFORBID')) {
	exit('forbid');
}

class TbBlock implements CronInterface {

	function sql() {
		return 'select b.id,b.kw,b.fid,b.type,b.value,b.zid,z.cookie,z.tbs,z.name from tb_block b inner join tb_zh z on b.zid=z.id and z.status=1 and b.status=0 order by z.order desc limit 2';
	}

	function run($resi, &$condition, &$info) {
		$rs = Tieba::blockStatic($resi['kw'], $resi['fid'], $resi['cookie'], $resi['tbs'], $resi['type'], $resi['value']);

		$condition = isset($rs['un']) && $rs['error_code'] == 0;

		$info = [$resi['zid'], '贴吧封禁', $resi['name'] . '-' . $resi['value'], $resi['kw'], $rs['error_msg']];

		//$info = [$resi['name'] . '-' . $resi['value'], '贴吧封禁:' . $resi['kw'], $rs['error_msg']];

	}

	function end($idstr, $status) {

		Db::getInstance()->exec("update tb_block set status={$status} where id in({$idstr})");

	}

}

?>