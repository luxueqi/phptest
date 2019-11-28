<?php

if (!defined('EXITFORBID')) {
	exit('forbid');
}

interface CronInterface {

	public function sql();
	public function run($resi, &$condition, &$info);
}

class TbGz implements CronInterface {

	private $tieba;

	function sql() {
		return 'SELECT z.uid,z.cookie,z.tbs,g.fid,g.name,g.id,z.name un from tb_zh z INNER JOIN tb_gz g on g.zid=z.id and g.status=0 and z.status=1 order by z.order desc LIMIT 3';
	}

	function run($resi, &$condition, &$info) {

		if (!$this->tieba) {
			$this->tieba = new Tieba();
		}

		$rs = $this->tieba->sign($resi['cookie'], $resi['tbs'], $resi['fid'], $resi['uid'], $resi['name']);

		$condition = isset($rs['error_code']) && ($rs['error_code'] == '160002' || $rs['error_code'] == '0');

		$info = ['贴吧签到:' . $resi['un'], $resi['name'], $rs['error_msg']];

	}

}

class TbBlock implements CronInterface {

	function sql() {
		return 'select b.id,b.kw,b.fid,b.type,b.value,z.cookie,z.tbs,z.name from tb_block b inner join tb_zh z on b.zid=z.id and z.status=1 and b.status=0 order by z.order desc limit 2';
	}

	function run($resi, &$condition, &$info) {
		$rs = Tieba::blockStatic($resi['kw'], $resi['fid'], $resi['cookie'], $resi['tbs'], $resi['type'], $resi['value']);

		$condition = isset($rs['un']) && $rs['error_code'] == 0;

		$info = ['贴吧封禁:' . $resi['name'] . '-' . $resi['value'], $resi['kw'], $rs['error_msg']];

	}

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
		$info = '';
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
				if (time() - $today < 1500) {
					$tt = $this->db('wsetting')->filed('v')->where('k="cron_time"')->getOne()['v'];
					if ($today > $tt) {
						foreach ($plist as $value) {

							Db::getInstance()->exec("update {$value} set status=0");

						}
						Db::getInstance()->exec("update wsetting set v='{$today}' where k='cron_time'");
					}
				}

				foreach ($plist as $value) {
					$info .= $this->commWork($value) . '-';
				}

			}

			$info = 'ok-' . rtrim($info, '-');
			echo "ok";

		} catch (PDOException $e) {
			echo "no-s";
			$info = 'no-' . $info . $e->getMessage();

		}

		$this->db('tb_cron')->filed('time,info')->where("(:time,'{$info}')", [':time' => time()])->save();

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

	private function commWork($table) {
		try {

			$class_name = '';

			if (strripos($table, '_') !== false) {
				$ls = explode('_', $table);

				foreach ($ls as $value) {
					$class_name .= ucwords($value);

				}
			} else {
				$class_name = $table;
			}

			$classc = new $class_name();

			$res = Db::getInstance()->exec($classc->sql())->getAll();

			if (empty($res)) {

				return "[{$table}-完成]";
			}

			$idstatus = ['y' => '', 'n' => ''];
			for ($i = 0, $len = count($res); $i < $len; $i++) {

				$rs = $classc->run($res[$i], $condition, $info);

				if ($condition) {

					$idstatus['y'] .= $res[$i]['id'] . ',';

				} else {
					$this->rwinfo($info[0], $info[1], $info[2]);

					$idstatus['n'] .= $res[$i]['id'] . ',';

				}

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
				Db::getInstance()->exec("update {$table} set status={$status} where id in({$value})");

			}
			return "[$table]";

		} catch (PDOException $ee) {
			return '[' . $table . '-' . $ee->getMessage() . ']';
			//exitMsg(ErrorConst::API_CATCH_ERRNO, 'fail');
		} catch (Exception $e) {
			return "[{$table}-" . $e->getMessage() . ']';
			//exitMsg(ErrorConst::API_CATCH_ERRNO, $e->getMessage());
		}

	}

	private function rwinfo($un, $kw, $msg) {
		$time = time();
		$this->db('werrinfo')->filed('name,t_name,errinfo,time')->where("('{$un}','{$kw}','{$msg}',$time)")->save();
	}

}

?>