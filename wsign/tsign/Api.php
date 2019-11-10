<?php

/**
 *
 */
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class Api extends WsignBase {

	function __construct() {

	}

	public function add() {

		if (isGetPostAjax('post')) {
			$param = $this->checkParams(['token' => 'regex:^[0-9a-zA-Z]{32}$', 'cookie' => 'noempty']);

			try {
				$res = $this->db('tb_user')->filed('id')->where("token='{$param['token']}'")->getOne();
				if (empty($res)) {
					exitMsg(2, '没有找到记录');
				}
				$tb = new Tieba($param['cookie']);
				$tbs = $tb->getTbs();
				$uidname = $tb->getUidName();
				$uid = $uidname['uid'];
				$info = $this->db('tb_zh')->filed('id')->where("uid={$uid}")->getOne();

				if (empty($info)) {
					//var_dump($uidname['uid']);exit();
					$time = time();
					$this->db('tb_zh')->filed('name,uid,cookie,tbs,time,w_id')->where("('{$uidname['name']}',$uid,:cookie,'$tbs',$time,{$res['id']})", [':cookie' => $param['cookie']])->save();

				} else {
					$this->db('tb_zh')->where("cookie='{$param['cookie']}',tbs='{$tbs}'")->save($info['id']);
				}
				//
				exitMsg(ErrorConst::API_SUCCESS_ERRNO, 'ok');

			} catch (PDOException $ee) {
				exitMsg(ErrorConst::API_CATCH_ERRNO, 'fail');
			} catch (Exception $e) {
				exitMsg(ErrorConst::API_CATCH_ERRNO, $e->getMessage());
			}

		}

		$this->view('tadd');
	}
	public function gzero() {
		$r = 0;
		if (G('q') == 1) {
			$r = Db::getInstance()->exec('update tb_gz set status=0 where status=2')->rowCount();
		} else {
			$today = mktime(0, 0, 0);
			$tt = $this->db('wsetting')->filed('v')->where('k="save2"')->getOne()['v'];

			if ($today > $tt) {
				$r = Db::getInstance()->exec('update tb_gz set status=0')->rowCount();
				Db::getInstance()->exec("update wsetting set v='{$today}' where k='save2'");
			}
		}

		echo $r;
	}

	public function sign() {
		try {
			$res = Db::getInstance()->exec('SELECT z.uid,z.cookie,z.tbs,g.fid,g.name,g.id from tb_zh z INNER JOIN tb_gz g on g.zid=z.id and g.status=0 and z.status=1 LIMIT 2')->getAll();
			if (empty($res)) {
				exitMsg(ErrorConst::API_SUCCESS_ERRNO, 'ok-ok');
			}
			$tb = new Tieba();
			for ($i = 0, $len = count($res); $i < $len; $i++) {

				$rs = $tb->sign($res[$i]['cookie'], $res[$i]['tbs'], $res[$i]['fid'], $res[$i]['uid'], $res[$i]['name']);
				$status = 0;
				if (isset($rs['error_code']) && ($rs['error_code'] == '160002' || $rs['error_code'] == '0')) {
					$status = 1;

				} else {
					$status = 2;

				}
				$this->db('tb_gz')->where('status=' . $status)->save($res[$i]['id']);
				if ($i == $len - 1) {
					break;
				}

				sleep(2);
			}
			echo "ok";

		} catch (PDOException $ee) {
			exitMsg(ErrorConst::API_CATCH_ERRNO, 'fail');
		} catch (Exception $e) {
			exitMsg(ErrorConst::API_CATCH_ERRNO, $e->getMessage());
		}

	}
}

?>