<?php

if (!defined('EXITFORBID')) {
	exit('forbid');
}

/**
 *
 */
class Api extends WsignBase {

	public function index() {
		$res = Db::getInstance(C('dbsh'))->exec('select name,price,count from test_product')->getOne();

		$this->assign('info', $res);

		$this->view('test-list');

	}

	private function checkKc($id) {

		$res = Db::getInstance(C('dbsh'))->exec('select name,price,count from test_product where id=?', [$id])->getOne();

		if (empty($res) || $res['count'] < $params['count']) {
			//exitMsg(-1, );

			die('商品不存在或库存不足');

		}
		return $res;
	}

	public function c() {
		$ps = ['name' => '', 'price' => 0, 'count' => 0, 'ajg' => 0, 'order_no' => ''];

		if (isGetPostAjax('post')) {
			$params = $this->checkParams(['id' => 'int', 'count' => 'int', 'ajiage' => 'regex:^[0-9]+(\.[0-9]+)?$']);
			$db = Db::getInstance(C('dbsh'));
			try {

				$res = $this->checkKc($params['id']);

				$rres = $db->exec('select order_no,pcount,price,payment from test_order where uid=? and spid=? and status=0', [Session('uid'), $params['id']])->getOne();

				if (!empty($rres)) {
					echo ("你有未支付的订单，请先支付<br>");
					$ps = ['name' => $res['name'], 'price' => $rres['price'], 'count' => $rres['pcount'], 'ajg' => $rres['payment'], 'order_no' => $rres['order_no']];

				} else {
					$ajiage = $params['count'] * $res['price'];

					$order_no = date('YmdHi') . mt_rand(100000, 999999);

					$time = time();
					$sql = "insert into test_order(uid,spid,order_no,payment,pcount,price,creat_time)values(?,?,?,?,?,?,$time)";

					$db->exec($sql, [Session('uid'), $params['id'], $order_no, $ajiage, $params['count'], $res['price']]);

					$ps = ['name' => $res['name'], 'price' => $res['price'], 'count' => $params['count'], 'ajg' => $ajiage, 'order_no' => $order_no];

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

			$res = Db::getInstance(C('dbsh'))->exec('select spid,status,payment from test_order where order_no=?', [G('order_no')])->getOne();
			if (empty($res)) {
				die('订单不存在');
			}
			$this->checkKc($res['spid']);
			if ($res['status'] == 1) {
				die('订单已支付');
			}
			if ($res['status'] == 2) {
				die('订单已关闭');
			}

			AliPay::pay(['subject' => '支付', 'body' => '', 'total_amount' => $res['payment'], 'out_trade_no' => G('order_no')]);

		}

	}

	public function notify() {
		AliPay::notify();
	}

	public function returnu() {
		AliPay::returnurl();
	}
}

?>