<?php
if (!defined('EXITFORBID')) {
	exit('forbid');
}

class Api extends WsignBase {
	protected $cachet = ['info' => ['time' => 900], 'tinfo' => ['time' => 900], 'einfo' => ['time' => 14400], 'binfo' => ['time' => 14400], 'cron' => ['time' => 1000], 'zinfo', 'winfo', 'dinfo', 'cls' => ['time' => 1200], 'top' => ['time' => 36000]];
	//protected $cachefalg = false;
	public function __construct() {
		//throw new Exception("Error Processing Request", 1);
		//$this->checkParams(['a' => 'int']);
		//$this->needLogin('/wsign-login-login.html');
		parent::__construct();
		//$this->cacheitem(['time' => 72000, 'qflag' => G('qflag', false)]);

	}
//SELECT w.id,u.name,w.t_name,w.status from wgz w INNER JOIN user u on u.id=w.uid
	//select $field from $table
	public function info() {
		$this->strsatusinfo('wgz', '签到');
		$this->slist('w.id,u.name,w.t_name,w.status', 'wgz w INNER JOIN user u on u.id=w.uid', 'info');
	}

	public function tinfo() {
		$this->strsatusinfo('tb_gz', '签到');

		$this->slist('g.id,z.name un,g.name,g.status', 'tb_gz g inner join tb_zh z on g.zid=z.id', 'tinfo');
	}

	public function zinfo() {
		$this->strsatusinfo('zd_sign', '签到');
		$this->slist('s.id,z.name,s.status', 'zd_sign s INNER JOIN tb_zh z on z.id=s.uid', 'zinfo');
	}

	public function winfo() {
		$this->strsatusinfo('wk_sign', '签到');
		$this->slist('s.id,z.name,s.status', 'wk_sign s INNER JOIN tb_zh z on z.id=s.uid', 'winfo');
	}

	public function dinfo() {
		$this->strsatusinfo('wb_day', '完成');
		$this->slist('s.id,z.name,s.status', 'wb_day s INNER JOIN user z on z.id=s.uid', 'dinfo');
	}

	public function cls() {
		$this->strsatusinfo('cron_list', '完成');
		$this->slist('id,cronname as name,status,isstop,endtime,`order`', 'cron_list', 'cronlist');
	}

	public function top() {

		$db = Db::getInstance();

		$rs = $db->exec('SELECT t.id,t.word,t.fid,t.status,t.lasttime,z.name,z.tbs,z.cookie from tb_top t INNER JOIN tb_zh z on t.uid=z.id')->getAll();

		//dump($rs);

		foreach ($rs as &$v) {
			$topendinfo = Tieba::topendTime($v['fid'], $v['cookie'], $v['tbs']);
			$v['endtime'] = $topendinfo['data']['bawu_task']['end_time'] + 0;
			$v['huitie'] = $topendinfo['data']['bawu_task']['task_list'][0]['task_status'];
			$v['bawu'] = $topendinfo['data']['bawu_task']['task_list'][1]['task_status'];
			// if ($endtime != 0 && $v['endtime'] != $endtime) {
			// 	//dump(11111111111111);
			// 	$v['endtime'] = $endtime;
			// 	$db->exec('update tb_top set endtime=? where id=?', [$v['endtime'], $v['id']]);
			// }

		}
		$this->strsatusinfo('tb_top', '置顶');

		$this->assign('list', $rs);

		$this->assign('count', count($rs));

		$this->view('toplist');

		//$this->slist('s.id,s.word,z.name,s.status,s.lasttime', 'tb_top s INNER JOIN tb_zh z on z.id=s.uid', 'toplist');
	}

	private function strsatusinfo($table, $type) {
		$res = Db::getInstance()->exec('select status,count(*) as c from ' . $table . ' group by status')->getAll();

		$liststatus = [0, 0, 0];

		foreach ($res as $value) {
			$liststatus[$value['status']] = $value['c'];
		}
		$strstatus = "已{$type}:{$liststatus[1]},未{$type}:{$liststatus[0]},失败:{$liststatus[2]}";
		$this->assign('strstatus', $strstatus);
	}

	public function corder() {

		$params = $this->checkParams(['id' => 'int', 'order' => 'int']);

		$this->db('cron_list')->where('`order`=:order', [':order' => $params['order']])->save($params['id']);

		exitMsg(ErrorConst::API_SUCCESS_ERRNO, '修改成功');

	}

	public function einfo() {
		//	$type = ['贴吧签到', ''];
		$this->slist('id,tb_wb_type,name,tb_wb_name,error,time', 'tb_wb_error order by id desc limit 30', 'einfo');
		//$this->slist('id,name,t_name as tname,errinfo info,time', 'werrinfo order by id desc limit 30', 'einfo');
	}

	public function binfo() {
		$this->strsatusinfo('tb_block', '封禁');
		$this->slist('b.id,b.kw,z.name,b.type,b.value,b.status', 'tb_block b inner join tb_zh z on z.id=b.zid', 'binfo');
	}

	public function cron() {
		$this->slist('id,time,info', 'tb_cron order by id desc limit 30', 'cron');
	}

	public function del() {

		$this->comdel('wgz');
		/*$param = $this->checkParams(['id' => 'int'], ['id' => 'ID参数不合法']);
	try {
	Db::getInstance()->exec('delete from wgz where id=' . $param['id']);
	exitMsg(ErrorConst::API_SUCCESS_ERRNO, '删除成功');

	} catch (PDOException $e) {
	exitMsg(ErrorConst::API_CATCH_ERRNO, 'fail');
	}*/
	}

	public function qzl() {

		if (!isGetPostAjax('post')) {

			exitMsg(ErrorConst::API_ERRNO, 'method err');

		}

		Request::Csrf();

		$param = $this->checkParams(['id' => 'regex:^[01]$']);

		$tablename = ['tb_gz', 'wgz'][$param['id']];

		try {

			$line = Db::getInstance()->exec("update {$tablename} set status=0 where status=2")->rowCount();

			if ($line > 0) {
				Db::table('cron_list')->where('cronname=?', [$tablename])->update(['status' => 0]);
			}

			exitMsg(ErrorConst::API_SUCCESS_ERRNO, $line);

		} catch (PDOException $e) {
			exitMsg(ErrorConst::API_CATCH_ERRNO, 'fail');
		}
	}

	public function td() {

		$this->comdel('tb_gz');
	}

	public function bd() {

		$this->comdel('tb_block');
	}

	public function status() {
		$this->statuscomm('wgz');

	}

	public function ts() {
		$this->statuscomm('tb_gz');
	}
	public function topcs() {
		$this->statuscomm('tb_top');
	}

	public function bs() {
		$this->statuscomm('tb_block');
	}

	public function zs() {
		//dump(G('status'));
		/*if (G('status') != 0) {
		Db::getInstance()->exec('update cron_list set status=0 where cronname="zd_sign"');
		}*/

		$this->statuscomm('zd_sign');

	}
	public function ws() {
		/*if (G('status') != 0) {
		Db::getInstance()->exec('update cron_list set status=0 where cronname="wk_sign"');
		}*/

		$this->statuscomm('wk_sign');

	}
	public function cs() {
		$this->statuscomm('cron_list');
	}

	public function sstop() {
		//dump($_SERVER);
		$this->statuscomm('cron_list', 'isstop');
	}

	public function ds() {
		$this->statuscomm('wb_day');
	}

}

?>