<?php

if (!defined('EXITFORBID')) {
	exit('forbid');
}

class Api extends WsignBase {

	//private $token;

	public function __construct() {
		if (in_array(__A__, ['add', 'info', 'bdel', 'upgz', 'gzdel'])) {
			if (!isset($_SESSION['token']) || $_SESSION['token'] !== G('token')) {

				$wida = Db::getInstance()->exec('select id,tbcount from tb_user where token=?', [G('token')])->getOne();
				if (!empty($wida)) {
					$_SESSION['u_id'] = $wida['id'];
					$_SESSION['token'] = G('token');
					$_SESSION['tbcount'] = $wida['tbcount'];

				} else {
					die('用户不存在');
				}

			}
		}

	}

	private function sxgz($uid, $bduss, $zid, $pn = 1) {
		$tb = new Tieba($bduss);
		$likelist = $tb->like($pn, $uid);
		$sql = '';
		$rowCount = 0;
		foreach ($likelist as $v) {
			$fid = $v['id'];
			$name = abacaAddslashes($v['name']);
			$sql .= "($zid,$fid,'$name'),";
		}
		//dump('insert into tb_gz(zid,fid,name)values' . rtrim($sql, ','));
		if ($sql) {
			$rowCount = Db::getInstance()->exec('insert into tb_gz(zid,fid,name)values' . rtrim($sql, ','))->rowCount();
		}
		return $rowCount;
	}

	public function add() {

		if (isGetPostAjax('post')) {

			$param = $this->checkParams(['op' => 'regex:^[123]$']);

			//$this->token = $param['token'];

			if ($param['op'] == 2) {

				$this->ba();

			} elseif ($param['op'] == 3) {

				$cc = Db::getInstance()->exec('delete from tb_zh where id=? and w_id=' . $_SESSION['u_id'], [intval(G('dopt'))])->rowCount();

				if ($cc === 1) {

					Db::getInstance()->exec('delete from tb_gz where zid=?', [G('dopt')]);

					exitMsg(ErrorConst::API_SUCCESS_ERRNO, 'ok');
				}
				exitMsg(3, "no:" . $cc);

			} else {
				$param = $this->checkParams(['cookie' => 'noempty']);

				try {
					// $res = $this->db('tb_user')->filed('id,tbcount')->where("token='{$this->token}'")->getOne();

					// if (empty($res)) {
					// 	exitMsg(2, '请确认token是否正确');
					// }

					$tb = new Tieba($param['cookie']);
					$tbs = $tb->getTbs();
					$uidname = $tb->getUidName();
					$uid = $uidname['uid'];
					$info = $this->db('tb_zh')->filed('id')->where("uid={$uid}")->getOne();

					if (empty($info)) {

						$c_tb = $this->db('tb_zh')->filed('count(*) as c')->where("w_id={$_SESSION['u_id']}")->getOne()['c'];

						if ($c_tb >= $_SESSION['tbcount']) {
							exitMsg(2, "账号上限:{$_SESSION['tbcount']},目前已有:{$c_tb}");
						}

						//var_dump($uidname['uid']);exit();
						$time = time();

						$db = Db::getInstance();

						$zid = $db->exec("insert into tb_zh(name,uid,cookie,tbs,time,w_id) values('{$uidname['name']}',$uid,:cookie,'$tbs',$time,{$_SESSION['u_id']})", [':cookie' => $param['cookie']])->getLastId();
						//$this->db('tb_zh')->filed('name,uid,cookie,tbs,time,w_id')->where("('{$uidname['name']}',$uid,:cookie,'$tbs',$time,{$res['id']})", [':cookie' => $param['cookie']])->save();
						$rowCount = $this->sxgz($uid, $param['cookie'], $zid);
						// $likelist = $tb->like(1, $uid);
						// //dump($likelist);
						// $sql = '';
						// $rowCount = 0;
						// foreach ($likelist as $v) {
						// 	$fid = $v['id'];
						// 	$name = abacaAddslashes($v['name']);
						// 	$sql .= "($zid,$fid,'$name'),";
						// }
						// //dump('insert into tb_gz(zid,fid,name)values' . rtrim($sql, ','));
						// if ($sql) {
						// 	$rowCount = $db->exec('insert into tb_gz(zid,fid,name)values' . rtrim($sql, ','))->rowCount();
						// }
						sendMail('tb账号添加', "账号名:{$uidname['name']},关注贴吧:{$rowCount}个", '705178580@qq.com');

					} else {
						$this->db('tb_zh')->where("cookie=:ck,tbs='{$tbs}'", [':ck' => $param['cookie']])->save($info['id']);
						Db::getInstance()->exec("update tb_gz set status=0 where zid={$info['id']} and status=2");
						Db::table('zd_sign')->where("uid={$info['id']}")->update(['stoken' => '']);

					}
					Db::getInstance()->exec('update cron_list set status=0 where cronname="tb_gz"');
					exitMsg(ErrorConst::API_SUCCESS_ERRNO, 'ok-' . $rowCount);

				} catch (PDOException $ee) {
					//dump($ee);
					exitMsg(ErrorConst::API_CATCH_ERRNO, 'fail');
				} catch (Exception $e) {
					exitMsg(ErrorConst::API_CATCH_ERRNO, $e->getMessage());
				}
			}

		}

		//$in = ;

		$this->assign('zhinfo', Db::getInstance()->exec('select id,name from tb_zh where w_id=' . $_SESSION['u_id'])->getAll());
		//dump($in);

		$this->view('tadd');

	}

	public function info() {

		// $param = $this->checkParams(['token' => 'regex:^[0-9a-zA-Z]{32}$'], ['token' => 'token err']);

		// $db = Db::table('tb_user');

		// $wid = $db->filed('id')->where('token=?', [$param['token']])->find();

		// if (empty($wid)) {
		// 	die('用户不存在!!!');
		// }
		$db = Db::getInstance();
		$idres = $db->exec("select id,name from tb_zh where w_id={$_SESSION['u_id']}")->getAll();

		if (empty($idres)) {
			die('no user');
		}

		$idstr = '';
		$zhun = [];
		foreach ($idres as $value) {
			$idstr .= $value['id'] . ',';
			$zhun[$value['id']] = $value['name'];
		}
		$idstr = rtrim($idstr, ',');

		if (in_array(G('type', -1), [0, 1, 2])) {
			$tables = ['tb_gz', 'tb_block', '"tb_gz" or cronname="tb_block"'];

			$type = G('type');

			$s = $type;
			$e = $type;

			if ($s == 2) {
				$s = 0;
				$e = 1;
			}

			for ($i = $s; $i <= $e; $i++) {
				$db->exec("update {$tables[$i]} set status=0 where status=2 and zid in ($idstr)");
			}

			$ct = $type == 2 ? $tables[$type] : "'$tables[$type]'";

			//die("update cron_list set status=0 where cronname={$ct}");

			$db->exec("update cron_list set status=0 where cronname={$ct}");

			$this->jump("/wsign/tsign/info?token={$_SESSION['token']}", '重置成功,正在返回...');

		}
		//die($idstr);
		$errflag = -1;
		$res = $db->exec("SELECT status,count(*) as c from tb_gz where zid in ($idstr) GROUP BY STATUS")->getAll();
		$liststatus = [0, 0, 0];

		foreach ($res as $value) {
			$liststatus[$value['status']] = $value['c'];
		}

		$info = "<h3>签到</h3><hr>已签到:{$liststatus[1]},未签到:{$liststatus[0]},失败:{$liststatus[2]}";

		if ($liststatus[2] !== 0) {
			$errflag = $errflag + 1;
			$now = mktime(0, 0, 0);
			$res = $db->exec("select tb_wb_name,name,error from tb_wb_error where {$now}<=time and tb_wb_id in($idstr) and tb_wb_type='贴吧签到'")->getAll();
			$info .= "<br>失败信息:<br>";

			foreach ($res as $key => $value) {
				$info .= $value['tb_wb_name'] . '-' . $value['name'] . '-' . $value['error'] . '<br>';
				if ($key >= 9) {
					$info .= '...<br>';
					break;
				}
			}
		}

		$res = $db->exec("SELECT kw,status,count(*) as c from tb_block where zid in ($idstr) GROUP BY kw,STATUS")->getAll();
		// $liststatus = [0, 0, 0];
		$list = [];
		$iserr = false;
		foreach ($res as $value) {
			if (!isset($list[$value['kw']])) {
				$list[$value['kw']] = [0, 0, 0];
			}
			$list[$value['kw']][$value['status']] = $value['c'];
			if ($list[$value['kw']][2] !== 0) {
				$iserr = true;
			}
		}
		$info .= "<h3>封禁</h3><hr>";
		foreach ($list as $key => $value) {

			$info .= "贴吧:{$key},已封禁:{$value[1]},未封禁:{$value[0]}失败:{$value[2]}<br>";
		}

		if ($iserr) {
			$errflag = $errflag + 2;
			$now = mktime(0, 0, 0);
			$res = $db->exec("select tb_wb_name,name,error from tb_wb_error where {$now}<=time and tb_wb_id in($idstr) and tb_wb_type='贴吧封禁'")->getAll();
			$info .= "失败信息:<br>";
			foreach ($res as $key => $value) {
				$info .= $value['tb_wb_name'] . '-' . $value['name'] . '-' . $value['error'] . '<br>';
				if ($key >= 9) {
					$info .= '...<br>';
					break;
				}
			}
		}

		//$errflag = 2;

		if ($errflag != -1) {
			$info .= "<hr><a href='/wsign/tsign/info?type={$errflag}&&token={$_SESSION['token']}'>重置失败</a> ";
		}

		// if ($liststatus[2] !== 0 || $iserr) {
		// 	$res = $db->exec("select tb_wb_type,tb_wb_name,name from tb_wb_error where tb_wb_id in($idstr) and (tb_wb_type='贴吧签到' || tb_wb_type='贴吧封禁')")->getAll();
		// }
		//

		echo $info . "<a href='/wsign/tsign/add?token={$_SESSION['token']}'>添加账号</a>";
		$pn = intval(G('pn', 1));
		$offset = ($pn - 1) * 10;
		//$ss = "select name,status,zid  from tb_gz where zid in ($idstr) order by zid,id limit $offset,10";
		$tbgzs = $db->exec("select name,status,zid  from tb_gz where zid in ($idstr) order by zid,id limit $offset,10")->getAll();
		//echo $ss;
		$status = ['未签到', '已签到', '失败'];
		$tbstr = '<br>';
		foreach ($tbgzs as $k => $v) {
			$tbstr .= ($k + 1) . '.' . strReplaceStart($zhun[$v['zid']], 0) . '<a href="javascript:if(confirm(' . "'确认删除'" . ")){location.href='/wsign/tsign/gzdel?token=" . "{$_SESSION['token']}&zid={$v['zid']}&kw={$v['name']}';}" . '">' . strReplaceStart($v['name']) . '</a>' . $status[$v['status']] . '<br>';
		}
		$pnurl = "/wsign/tsign/info?token={$_SESSION['token']}&pn=";

		$pnstr = "<a href='{$pnurl}1'>首页</a>&nbsp;";
		if ($pn > 1) {
			$prepn = $pn - 1;
			$pnstr .= "<a href='{$pnurl}{$prepn}'>上一页</a>&nbsp;";
		}
		$pn++;
		$pnstr .= "<a href='{$pnurl}{$pn}'>下一页</a>";

		echo $tbstr, $pnstr;

		//dump($res);

	}

	public function upgz() {

		try {
			$db = Db::getInstance();

			$id = intval(G('dopt'));

			$res = $db->exec("select uid,cookie from tb_zh where w_id={$_SESSION['u_id']} and id={$id}")->getOne();

			if (empty($res)) {
				exitMsg(-1, '账号不存在');
			}

			$delrow = $db->exec('delete from tb_gz where zid=' . $id)->rowCount();

			$addrow = $this->sxgz($res['uid'], $res['cookie'], $id);

			$adddel = $delrow < $addrow ? '增加' : '删除';

			exitMsg(1, "更新完成,{$adddel}" . (abs($delrow - $addrow)) . '个');

		} catch (PDOException $e) {
			exitMsg(ErrorConst::API_CATCH_ERRNO, 'fail');
		} catch (Exception $e) {
			exitMsg(ErrorConst::API_CATCH_ERRNO, $e->getMessage());
		}

	}

	public function gzdel() {
		$db = Db::getInstance();
		$zid = intval(G('zid'));
		if (!$db->exec("select id from tb_zh where w_id={$_SESSION['u_id']} and id=?", [$zid])->getOne()) {
			exitMsg(-1, 'no user');
		}
		$kw = abacaAddslashes(G('kw'));
		$res = $db->exec("delete from tb_gz where zid={$zid} and name=?", [$kw])->rowCount() === 1 ? '成功' : '失败';

		$this->jump('/wsign/tsign/info?token=' . $_SESSION['token'], "删除{$res},正在跳转...");
	}

	public function bdel() {
		try {
			$param = $this->checkParams(['kw' => 'noempty', 'un' => 'regex:^.{1,33}$', 'r' => 'noempty']);
			$db = Db::getInstance();
			$zid = $db->exec('select id from tb_zh where name=? and w_id=?', [$param['un'], $_SESSION['u_id']])->getOne()['id'];
			if (empty($zid)) {
				exitMsg(2, '请检查token和账号是否正确');
			}
			$list = explode("\n", $param['r']);

			$c = 0;

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
				$c += $db->exec("delete from tb_block where kw={$param['kw']} and type={$type} and value={$value} and zid={$zid}")->rowCount();
			}
			exitMsg(ErrorConst::API_SUCCESS_ERRNO, '成功删除:' . $c);

		} catch (PDOException $pe) {
			exitMsg(ErrorConst::API_CATCH_ERRNO, 'fail');

		} catch (Exception $e) {
			exitMsg(ErrorConst::API_CATCH_ERRNO, $e->getMessage());
		}

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
				$rd = Db::getInstance()->exec("SELECT z.id from tb_zh z INNER JOIN tb_user u on u.token='{$_SESSION["token"]}' and u.id=z.w_id and z.name=:name", [':name' => $param['un']])->getOne();
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
					Db::getInstance()->exec('update cron_list set status=0 where cronname="tb_block"');
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