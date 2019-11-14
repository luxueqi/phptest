<?php

/**
 *
 */
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class Api extends WsignBase {

	private $token;

	public function add() {

		if (isGetPostAjax('post')) {
			$param = $this->checkParams(['token' => 'regex:^[0-9a-zA-Z]{32}$', 'op' => 'regex:^[12]$']);
			$this->token = $param['token'];
			if ($param['op'] == 2) {
				$this->ba();
			} else {
				$param = $this->checkParams(['cookie' => 'noempty']);

				try {
					$res = $this->db('tb_user')->filed('id')->where("token='{$this->token}'")->getOne();
					if (empty($res)) {
						exitMsg(2, '请确认token是否正确');
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

		}

		$this->view('tadd');

	}
	public function cron() {
		$plist = ['tb_gz', 'tb_block'];
		$today = mktime(0, 0, 0);
		//var_dump($today, time(), time() - $today);exit;
		try {
			if (G('q') == 1) {
				foreach ($plist as $value) {

					Db::getInstance()->exec("update {$value} set status=0 where status=2");

				}
			} elseif (G('q') == 2) {
				foreach ($plist as $value) {

					Db::getInstance()->exec("update {$value} set status=0");

				}
			} else {
				if (time() - $today < 2500) {
					$tt = $this->db('wsetting')->filed('v')->where('k="cron_time"')->getOne()['v'];
					if ($today > $tt) {
						foreach ($plist as $value) {

							Db::getInstance()->exec("update {$value} set status=0");

						}
						Db::getInstance()->exec("update wsetting set v='{$today}' where k='cron_time'");
					}
				}

				$this->sign();
			}

			echo "ok";
		} catch (PDOException $e) {
			echo "no";
			//echo $e->getMessage();
		}

	}
	/*public function gzero() {
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
	}*/

	private function sign() {
		try {
			$res = Db::getInstance()->exec('SELECT z.uid,z.cookie,z.tbs,g.fid,g.name,g.id from tb_zh z INNER JOIN tb_gz g on g.zid=z.id and g.status=0 and z.status=1 LIMIT 2')->getAll();
			if (empty($res)) {
				//exitMsg(ErrorConst::API_SUCCESS_ERRNO, 'ok-ok');
				return 'sign-ok';
			}
			$tb = new Tieba();
			$idstatus = ['y' => '', 'n' => ''];
			for ($i = 0, $len = count($res); $i < $len; $i++) {

				$rs = $tb->sign($res[$i]['cookie'], $res[$i]['tbs'], $res[$i]['fid'], $res[$i]['uid'], $res[$i]['name']);
				$status = 0;

				if (isset($rs['error_code']) && ($rs['error_code'] == '160002' || $rs['error_code'] == '0')) {
					//$status = 1;
					$idstatus['y'] .= $res[$i]['id'] . ',';

				} else {
					$idstatus['n'] .= $res[$i]['id'] . ',';

				}
				//$this->db('tb_gz')->where('status=' . $status)->save();
				if ($i == $len - 1) {
					break;
				}

				sleep(2);
			}

			foreach ($idstatus as $key => $value) {
				if ($value == '') {
					continue;
				}

				$value = rtrim($value, ',');
				$status = $key == 'y' ? 1 : 2;
				Db::getInstance()->exec("update tb_gz set status={$status} where id in({$value})");
				//var_dump("update tb_gz set status={$status} where id in({$idstatus[$key]})");exit;
			}

			//echo "ok";

		} catch (PDOException $ee) {
			echo 'sign-fail';
			//exitMsg(ErrorConst::API_CATCH_ERRNO, 'fail');
		} catch (Exception $e) {
			echo 'sign-' . $e->getMessage();
			//exitMsg(ErrorConst::API_CATCH_ERRNO, $e->getMessage());
		}

	}

	public function block() {
		//741aad1f
		//531438196
		//1920362691
		//1042496
		//948043122
		//tb.1.727668c3.DsiLvk5KFe6kwxlhtT7hHQ
		//tb.1.3881fd72.5_FH5O3JsjjsTj1iYLTtyw
		//
		//96cdea74
		//
		//SELECT z.id from tb_zh z INNER JOIN tb_user u on u.token='c83551352b1f091237fc91de6926697d' and u.id=z.w_id and z.id=2
		//
		//var_dump(json_decode(Tieba::blockStatic('凡雪', 1430795, 'H5lS0J3dlpEaWVkYVRYdGkydjRjR3RxU3pPUm5kYWdYVGhLTFZMczlkb01pcnRkSVFBQUFBJCQAAAAAAAAAAAEAAABA6A8AMTMxNDUyNwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAz9k10M~ZNda', '4b66fc9cc01876e21573633431', 'u', 1)));
		//echo Tieba::u2p(1);
	}

	private function ba() {
		if (isGetPostAjax('post')) {

			$param = $this->checkParams(['kw' => 'noempty', 'un' => 'regex:^.{1,16}$', 'r' => 'noempty']);

			try {
				$rd = Db::getInstance()->exec("SELECT z.id from tb_zh z INNER JOIN tb_user u on u.token='{$this->token}' and u.id=z.w_id and z.name=:name", [':name' => $param['un']])->getOne();
				//var_dump($rd);exit;
				if (empty($rd)) {
					exitMsg(2, '请确认token和管理账号是否正确');
				}
				$fid = (new Tieba())->getFid($param['kw']);
				$list = explode("\n", $param['r']);
				$sql = '';
				foreach ($list as $value) {
					if ($value == '') {
						continue;
					}

					$type = 0;

					if (strrpos($value, 'u:') === 0) {
						$type = 1;

						$value = Tieba::u2p(explode(':', $value)[1]);
					}
					$sql .= "('{$param['kw']}',{$fid},{$rd['id']},{$type},'{$value}'),";
				}
				if ($sql != '') {
					$sql = 'insert into tb_block(kw,fid,zid,type,value)values' . rtrim($sql, ',');
					//var_dump($sql);exit;
					Db::getInstance()->exec($sql)->rowCount();
					exitMsg(ErrorConst::API_SUCCESS_ERRNO, '添加成功');
				}
				exitMsg(2, '添加失败');

			} catch (PDOException $ee) {
				exitMsg(ErrorConst::API_CATCH_ERRNO, 'fail');
			} catch (Exception $e) {
				exitMsg(ErrorConst::API_CATCH_ERRNO, $e->getMessage());
			}
		}
		//$this->view('badd');
	}

}

?>