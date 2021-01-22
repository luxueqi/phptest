<?php

if (!defined('EXITFORBID')) {
	exit('forbid');
}

class TbTop implements CronInterface {

	function sql() {

		//$endtime =Tieba::topendTime

		//$time = time() - 10 * 24 * 3600;

		return 'SELECT t.id,t.word,t.fid,t.tid,t.uid,z.tbs,z.cookie,z.name from tb_top t INNER JOIN tb_zh z on  t.status=0 and t.uid=z.id and datediff(CURRENT_DATE,t.lasttime)>=10  LIMIT 1';
	}

	function run($resi, &$condition, &$info) {

		try {
			$info = [$resi['uid'], '贴吧置顶', $resi['name'], $resi['word'], ''];
			//$word, $fid, $bduss, $tbs, $tid

			$rs = Tieba::topStatic($resi['word'], $resi['fid'], $resi['cookie'], $resi['tbs'], $resi['tid']);

			//dump($rs);

			$condition = isset($rs['error_code']) && $rs['error_code'] == 0;

			if (!$condition) {
				$info[4] = $rs['error_code'] . '-' . $rs['error_msg'];
			}
		} catch (Exception $e) {
			$info[4] = $e->getMessage();
		}

	}

	function end($idstr, $status) {

		$tmp = $status == 1 ? 'CURRENT_DATE' : "'1970-01-01'";

		$filedtmp = "status={$status},lasttime={$tmp}";

		Db::getInstance()->exec("update tb_top set {$filedtmp} where id in({$idstr})");

	}
}
?>