<?php
if (!defined('EXITFORBID')) {
	exit('forbid');
}
/**
 *
 */
class Zhidao {
	public static function sign($cookie, $stoken = '') {

		if (empty($stoken)) {
			$stoken = self::getStoken($cookie);
		}

		if (!$stoken) {
			throw new Exception("stoken err");

		}

		$header = ['Cookie: ' . $cookie, 'Referer: https://zhidao.baidu.com/', 'X-Requested-With: XMLHttpRequest'];
		$data = 'cm=100509&utdata=47%2C47%2C21%2C20%2C21%2C30%2C16%2C47%2C23%2C18%2C18%2C22%2C10%2C31%2C22%2C22%2C15792816850380&stoken=' . $stoken;
		$http = new Http('https://zhidao.baidu.com/submit/user', $header, $data);
		return json_decode($http->http(), true);

	}

	public static function getStoken($cookie) {
		$h = new HttpHeader();
		$header = $h->setReferer('https://zhidao.baidu.com/')->setCookie($cookie)->isAjax(true)->getHeader();
		$http = new Http('https://zhidao.baidu.com/api/loginInfo?t=1579281717324', $header);
		$res = $http->http();
		return strMid('stoken":"', '"', $res);

	}

	// public static function chest($cookie, $stoken) {
	// 	$http = new Http();
	// 	return json_decode($http->request('https://zhidao.baidu.com/shop/submit/chest?type=SilverChest', 'itemId=129&stoken=' . $stoken, ['Cookie: ' . $cookie, 'Referer: https://zhidao.baidu.com/']), true);

	// }

}

?>