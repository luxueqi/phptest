<?php

if (!defined('EXITFORBID')) {
	exit('forbid');
}

/**
 *
 */
class Api extends WsignBase {
	public function __construct() {
		$this->needLogin('/wsign-login-login.html?r=/wsign-test-index.html', ['w' => ['index']]);
	}

	public function index() {
		//dump(Db::table('zd_sign')->where("uid=?")->getSql()->update(['stoken' => '']));

		$res = Db::getInstance()->exec('select id,name,price,count from test_product')->getAll();

		$this->assign('info', $res);

		$this->view('test-list');

	}
//检测库存
	private function checkKc($id, $count) {

		$res = Db::getInstance()->exec('select name,price,count from test_product where id=?', [$id])->getOne();

		//var_dump($res);

		if (empty($res) || $res['count'] < $count) {
			//exitMsg(-1, );
			$this->jump('/wsign-test-index.html', '商品不存在或库存不足,正在跳转...');
			//die();

		}
		return $res;
	}
//创建订单
	public function c() {
		$ps = ['name' => '', 'price' => 0, 'count' => 0, 'ajg' => 0, 'order_no' => '', 'wzf' => ''];

		if (isGetPostAjax('get')) {
			$params = $this->checkParams(['id' => 'int', 'count' => 'int', 'ajiage' => 'regex:^[0-9]+(\.[0-9]+)?$']);

			$db = Db::getInstance();
			//$uip = ip2long($_SERVER['REMOTE_ADDR']);
			$uid = Session('uid');
			try {
				//检查库存
				$res = $this->checkKc($params['id'], $params['count']);
				//检查当前用户的当前订单是否有未支付
				$rres = $db->exec('select order_no,pcount,price,payment from test_order where uid=? and spid=? and status=0', [$uid, $params['id']])->getOne();

				if (!empty($rres)) {
					//echo ("<p style='margin:15px 10px'></p>");

					$ps = ['name' => $res['name'], 'price' => $rres['price'], 'count' => $rres['pcount'], 'ajg' => $rres['payment'], 'order_no' => $rres['order_no'], 'wzf' => '<p style="color:red">你有未支付的订单，请先支付</p>'];

				} else {
					$ajiage = $params['count'] * $res['price'];

					$order_no = date('YmdHi') . mt_rand(100000, 999999);

					$time = time();
					$sql = "insert into test_order(uid,spid,order_no,payment,pcount,price,creat_time)values(?,?,?,?,?,?,$time)";

					$db->exec($sql, [$uid, $params['id'], $order_no, $ajiage, $params['count'], $res['price']]);

					$ps = ['name' => $res['name'], 'price' => $res['price'], 'count' => $params['count'], 'ajg' => $ajiage, 'order_no' => $order_no, 'wzf' => ''];

				}

			} catch (PDOException $pe) {
				//$db->rollback();
				die('c err');

			} catch (Exception $e) {
				die($e->getMessage());
			}

		}

		$this->assign('ps', $ps);

		$this->view('test-c');
	}

	public function pay() {
		if (isGetPostAjax('post')) {

			$res = Db::getInstance()->exec('select spid,status,payment,pcount from test_order where order_no=? and uid=' . Session('uid'), [G('order_no')])->getOne();
			if (empty($res)) {
				die('订单不存在');
			}
			//检查库存
			$this->checkKc($res['spid'], $res['pcount']);
			if ($res['status'] == 1) {
				die('订单已支付');
			}
			if ($res['status'] == 2) {
				die('订单已关闭');
			}

			AliPay::pay(['subject' => 'zhifu', 'body' => 'zhi', 'total_amount' => $res['payment'], 'out_trade_no' => G('order_no')]);

		}

	}

	public function notify() {
		AliPay::notify();
	}

	public function returnu() {
		AliPay::returnurl();
	}

	public function del() {
		if (isGetPostAjax('post')) {
			try {

				$param = $this->checkParams(['order_no' => 'noempty']);

				//$sql = 'delete from test_order where order_no=?';

				// $data = [$param['order_no']];

				// if (!Session('uid')) {
				// 	$sql = $sql . ' and uip=?';
				// 	$data[] = ip2long($_SERVER['REMOTE_ADDR']);
				// }

				if (Db::getInstance()->exec('delete from test_order where order_no=? and uid=?', [$param['order_no'], Session('uid')])->rowCount() == 1) {
					exitMsg(ErrorConst::API_SUCCESS_ERRNO, 'ok');

				}

				exitMsg(ErrorConst::API_ERRNO, 'no order_no');

			} catch (PDOException $e) {
				exitMsg(ErrorConst::API_CATCH_ERRNO, 'fail');
			}
		}

	}

	public function lis() {

		/*$sql = '';

		$data = [];

		if (isGetPostAjax('post') && !empty(G('pnum'))) {

		$sql = "select o.id,o.uip,p.name,o.order_no,o.pcount,o.price,o.payment,o.payment_type,o.status,o.payment_time,o.creat_time,o.platform_numbe from test_order o,test_product p where o.spid=p.id and o.platform_numbe=?";
		$data[] = G('pnum');
		}*/

		//if (isGetPostAjax('get')) {
		//$uid=;
		//$data = [];
		$sql = 'select p.name,o.order_no,o.pcount,o.price,o.payment,o.payment_type,o.status,o.payment_time,o.creat_time,o.platform_numbe from test_order o,test_product p where o.spid=p.id and o.uid=' . Session('uid');
		// if (!Session('uid')) {
		// 	$sql = $sql . ' and uip=?';
		// 	$data[] = ip2long($_SERVER['REMOTE_ADDR']);
		// }
		//}

		$res = Db::getInstance()->exec($sql)->getAll();

		$this->assign('count', count($res));
		$this->assign('list', $res);

		$this->view('test-li');
	}
}

?>