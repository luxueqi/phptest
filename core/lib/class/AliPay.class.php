<?php

if (!defined('EXITFORBID')) {
	exit('forbid');
}

require_once LIB_PATH . '/vendor/alipay/pagepay/service/AlipayTradeService.php';

require_once LIB_PATH . '/vendor/alipay/pagepay/buildermodel/AlipayTradePagePayContentBuilder.php';

class AliPay {

	public static function pay($info) {
		$payRequestBuilder = new AlipayTradePagePayContentBuilder();
		$payRequestBuilder->setBody($info['body']);
		$payRequestBuilder->setSubject($info['subject']);
		$payRequestBuilder->setTotalAmount($info['total_amount']);
		$payRequestBuilder->setOutTradeNo($info['out_trade_no']);
		$config = C('alipay');
		$aop = new AlipayTradeService($config);
		echo $aop->pagePay($payRequestBuilder, $config['return_url'], $config['notify_url']);

	}

	public static function notify() {
		//exit;
		$arr = $_POST;
		$config = C('alipay');
		$alipaySevice = new AlipayTradeService($config);
		$alipaySevice->writeLog(var_export($_POST, true));
		$result = $alipaySevice->check($arr, 1);
		if ($result) {
			//验证成功

			//交易状态
			$trade_status = $_POST['trade_status'];

			if ($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
				self::uporder();

			}

			echo "success"; //请不要修改或删除
		} else {
			//验证失败
			echo "fail";
		}

	}

	private static function uporder() {
		$db = null;
		try {
			//商户订单号

			$out_trade_no = G('out_trade_no');

			//支付宝交易号

			$trade_no = G('trade_no');
			$db = Db::getInstance();

			$res = $db->exec('select status,pcount,spid from test_order where order_no=?', [$out_trade_no])->getOne();

			if (empty($res)) {
				return 2;
			}
			//var_dump($e);exit;
			if ($res['status'] == 0) {

				if ($db->exec("update test_order set payment_type=1,status=1,payment_time=?,platform_numbe=? where order_no=? and status=0", [time(), $trade_no, $out_trade_no])->rowCount() === 1) {

					$db->exec('update test_product set count=count-? where id=?', [$res['pcount'], $res['spid']]);

				}

				//$db->beginTransaction()->exec("update test_order set payment_type=1,status=1,payment_time=?,platform_numbe=? where order_no=?", [time(), $trade_no, $out_trade_no])->exec('update test_product set count=count-? where id=?', [$res['pcount'], $res['spid']])->commit();
				//Db::table('test_order')->where('order_no=?', [$out_trade_no])->update(['payment_time' => time(), 'platform_numbe' => '?'], [$trade_no]);
				//$db->exec("update test_order set payment_type=1,status=1,payment_time=?,platform_numbe=? where order_no=?", [time(), $trade_no, $out_trade_no])->exec('update test_product set count=count-? where id=?', [$res['pcount'], $res['spid']]);

			}

			return 1;
		} catch (PDOException $e) {

			//$db->rollback();

			return 0;

		}

	}

	public static function returnurl() {

		$arr = $_GET;
		$config = C('alipay');
		$alipaySevice = new AlipayTradeService($config);
		// var_dump($arr);
		// exit;
		$result = $alipaySevice->check($arr, 1);

		/* 实际验证过程建议商户添加以下校验。
		1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号，
		2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额），
		3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）
		4、验证app_id是否为该商户本身。
		 */
		if ($result) {
			//验证成功
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			//请在这里加上商户的业务逻辑程序代码

			//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
			//获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表

			//商户订单号
			$out_trade_no = htmlspecialchars($_GET['out_trade_no']);

			//支付宝交易号
			$trade_no = htmlspecialchars($_GET['trade_no']);
			$s = self::uporder();
			$rs = '';
			if ($s === 1) {
				$rs = "支付成功<br />支付宝交易号：{$trade_no}<br />商户订单号：{$out_trade_no}";
			} elseif ($s === 0) {
				$rs = "up order error";
			} elseif ($s === 2) {
				$rs = "订单不存在";
			}
			echo $rs . "<p><a href='/wsign-test-index.html'>返回商品页面</a></p>";
			//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		} else {
			//验证失败
			echo "验证失败";
		}
	}

}

?>