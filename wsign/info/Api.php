<?php
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class Api extends WsignBase {
	protected $cachet = ['info' => ['time' => 1800], 'tinfo' => ['time' => 1800], 'einfo' => ['time' => 14400], 'binfo' => ['time' => 14400], 'cron'];
	//protected $cachefalg = false;
	public function __construct() {

		$this->needLogin('/wsign-login-login.html');
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

	private function strsatusinfo($table, $type) {
		$res = Db::getInstance()->exec('select status,count(*) as c from ' . $table . ' group by status')->getAll();

		$liststatus = [0, 0, 0];

		foreach ($res as $value) {
			$liststatus[$value['status']] = $value['c'];
		}
		$strstatus = "已{$type}:{$liststatus[1]},未{$type}:{$liststatus[0]},失败:{$liststatus[2]}";
		$this->assign('strstatus', $strstatus);
	}

	public function einfo() {
		$this->slist('id,name,t_name as tname,errinfo info,time', 'werrinfo', 'einfo');
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

	public function bs() {
		$this->statuscomm('tb_block');
	}

}

?>