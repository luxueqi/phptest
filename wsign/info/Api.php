<?php
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class Api extends WsignBase {
	public function __construct() {

		$this->needLogin('/wsign-login-login.html');

	}
//SELECT w.id,u.name,w.t_name,w.status from wgz w INNER JOIN user u on u.id=w.uid
	//select $field from $table
	public function info() {
		$this->slist('w.id,u.name,w.t_name,w.status', 'wgz w INNER JOIN user u on u.id=w.uid', 'info');
	}

	public function tinfo() {
		$this->slist('g.id,z.name un,g.name,g.status', 'tb_gz g inner join tb_zh z on g.zid=z.id', 'tinfo');
	}

	public function einfo() {
		$this->slist('id,name,t_name as tname,errinfo info,time', 'werrinfo', 'einfo');
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

	public function status() {
		$this->statuscomm('wgz');
	}

	public function ts() {
		$this->statuscomm('tb_gz');
	}

}

?>