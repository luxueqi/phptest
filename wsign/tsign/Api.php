<?php

if (!defined('EXITFORBID')) {
	exit('forbid');
}

class Api extends WsignBase {

	private $token;

	public function add() {

		if (!G('token')) {
			die('参数错误');
		}

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
						sendMail('tb账号添加', "账号名:{$uidname['name']}", '705178580@qq.com');

					} else {
						$this->db('tb_zh')->where("cookie=:ck,tbs='{$tbs}'", [':ck' => $param['cookie']])->save($info['id']);
						Db::table('zd_sign')->where("uid={$info['id']}")->update(['stoken' => '']);
					}
					//
					exitMsg(ErrorConst::API_SUCCESS_ERRNO, 'ok');

				} catch (PDOException $ee) {
					//dump($ee);
					exitMsg(ErrorConst::API_CATCH_ERRNO, 'fail');
				} catch (Exception $e) {
					exitMsg(ErrorConst::API_CATCH_ERRNO, $e->getMessage());
				}
			}

		}

		$this->view('tadd');

	}

	private function cronlist($status = false) {
		$db = Db::table('cron_list');
		if ($status === false) {
			return $db->filed('cronname')->where('isstop=0')->select();
		}
		return $db->filed('cronname')->where('status=0 and isstop=0 order by `order` desc')->select();
	}
	public function cron() {
		//$plist = ['tb_gz', 'tb_block'];
		$today = mktime(0, 0, 0);
		$info = '';
		//var_dump($today, time(), time() - $today);exit;
		try {
			if (G('q') == 1 || G('q') == 2) {

				$plistc = $this->cronlist();

				foreach ($plistc as $value) {
					$tbname = $value['cronname'];
					if (G('q') == 1) {

						Db::getInstance()->exec("update {$tbname} set status=0 where status=2");
					} elseif (G('q') == 2) {
						Db::getInstance()->exec("update {$tbname} set status=0");
					}

				}
			} else {
				if (time() - $today < 3700) {

					$tt = $this->db('wsetting')->filed('v')->where('k="cron_time"')->getOne()['v'];
					if ($today > $tt) {

						$plist = $this->cronlist();

						array_push($plist, ['cronname' => 'cron_list']);

						//dump($plist);

						foreach ($plist as $value) {
							$tbname = $value['cronname'];
							Db::getInstance()->exec("update {$tbname} set status=0");

						}
						//exit;
						Db::getInstance()->exec("update wsetting set v='{$today}' where k='cron_time'");
					}
				}
				$plist = $this->cronlist(true);
				//dump($plist);
				//
				define('__INTERFACE__', dirname(__FILE__) . '/interface/');

				require_once __INTERFACE__ . 'Interface.php';

				foreach ($plist as $value) {
					$tbname = $value['cronname'];
					$info .= $this->commWork($tbname) . '-';
					//var_dump($tbname, $info);
				}
				if (empty($plist)) {
					$info = 'all-';
				}
			}

			$info = 'ok-' . rtrim($info, '-');
			echo "ok";

		} catch (PDOException $e) {
			echo "no-s";
			$info = 'no-' . $info . $e->getMessage();

		}

		$this->db('tb_cron')->filed('time,info')->where("(:time,:info)", [':time' => time(), ':info' => $info])->save();

	}

	private function ba() {
		if (isGetPostAjax('post')) {

			$param = $this->checkParams(['kw' => 'noempty', 'un' => 'regex:^.{1,33}$', 'r' => 'noempty']);

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
					} elseif (strrpos($value, 'p:') === 0) {
						$type = 1;

						$value = explode(':', $value)[1];
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
				$class_name = ucwords($table);
			}

			require_once __INTERFACE__ . $class_name . '.Cron.php';

			$classc = new $class_name();

			$res = Db::getInstance()->exec($classc->sql())->getAll();

			if (empty($res)) {

				Db::table('cron_list')->where("cronname='{$table}'")->update(['status' => 1, 'endtime' => time()]);

				return "[{$table}-完成]";
			}

			$idstatus = ['y' => '', 'n' => ''];
			for ($i = 0, $len = count($res); $i < $len; $i++) {

				$rs = $classc->run($res[$i], $condition, $info);

				if ($condition) {

					$idstatus['y'] .= $res[$i]['id'] . ',';

				} else {
					$this->rwinfo($info[0], $info[1], $info[2], $info[3], $info[4]);

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

				$classc->end($value, $status);
				// $filedtmp = "status={$status}";
				// if ($table == 'tb_top') {
				// 	$filedtmp = $filedtmp . ",lasttime=" . ($status == 1 ? time() : 0);
				// }
				// Db::getInstance()->exec("update {$table} set {$filedtmp} where id in({$value})");

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

	// private function rwinfo($un, $kw, $msg) {
	// 	$time = time();
	// 	$this->db('werrinfo')->filed('name,t_name,errinfo,time')->where("('{$un}','{$kw}','{$msg}',$time)")->save();
	// }
	private function rwinfo($tb_wb_id, $tb_wb_type, $name, $tb_wb_name, $error) {
		//$time = time();
		$data = [$tb_wb_id + 0, $tb_wb_type, ':name', ':tbwbname', ':error', time()];
		Db::table('tb_wb_error')->filed('tb_wb_id, tb_wb_type, name, tb_wb_name, error,time')->insert($data, [':name' => $name, ':tbwbname' => $tb_wb_name, ':error' => $error]);
		//$this->db('werrinfo')->filed('name,t_name,errinfo,time')->where("('{$un}','{$kw}','{$msg}',$time)")->save();
	}

}

?>