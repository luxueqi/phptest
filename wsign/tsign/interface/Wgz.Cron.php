<?php

if (!defined('EXITFORBID')) {
	exit('forbid');
}

class Wgz implements CronInterface {

	private $wb;

	function sql() {
		return 'SELECT w.id,w.m_name,w.t_name,w.uid,u.cookie,u.name from wgz w inner join user u on w.uid=u.id and u.status=1 and w.status=0  LIMIT  2';
	}

	function run($resi, &$condition, &$info) {

		if (!$this->wb) {
			$this->wb = new Weibo();
		}
		$info = [$resi['uid'], '微博签到', $resi['name'], $resi['t_name'], ''];

		//$info = [$resi['name'], '微博签到:' . $resi['t_name'], ''];

		$rs = json_decode($this->wb->sign($resi['m_name'], $resi['cookie']), true);

		//$rs = json_decode($rs, true);

		if (!$rs) {
			$info[4] = 'cookie失效或网络错误';
		} else {

			$condition = isset($rs['code']) && ($rs['code'] == '100000' || $rs['code'] == '382004');

			if (!$condition) {

				$info[4] = $rs['msg'];
			}
		}

		//dump($rs);exit;

	}

	function end($idstr, $status) {

		Db::getInstance()->exec("update wgz set status={$status} where id in({$idstr})");

	}

}

?>