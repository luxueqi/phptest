<?php
require_once './init.php';

$http = new Http();

if (G('sign') == '') {
	$urlcode = 'https://passport.baidu.com/v2/api/getqrcode?lp=pc&qrloginfrom=pc&gid=1577DB5-5C58-4CA6-BBE3-7733856F8BC1&apiver=v3&tt=1577606087058&tpl=tb&_=1577606087065';

	$sign = json_decode($http->request($urlcode), true)['sign'];

	$urlimg = 'https://passport.baidu.com/v2/api/qrcode?sign=' . $sign . '&lp=pc&qrloginfrom=pc';

	echo "<h2 style='text-align:center'>百度贴吧BDUSS在线获取</h2><hr><center><a href='/bduss'>刷新验证码</a><p><img style='margin:5px auto' src='$urlimg'/></p><a href='/bduss?sign={$sign}'>先扫码确认登陆，再点击这个！！！</a></center>";
} else {

	$urlnicast = 'https://passport.baidu.com/channel/unicast?channel_id=' . G('sign') . '&tpl=tb&gid=1577DB5-5C58-4CA6-BBE3-7733856F8BC1&callback=tangram_guid_1577606086641&apiver=v3&tt=1577606148507&_=1577606148507';
	$bduss = strMid('\"v\":\"', '\",', $http->request($urlnicast));

	if ($bduss) {

		$url = 'https://passport.baidu.com/v3/login/main/qrbdusslogin?v=1577607039317&bduss=' . $bduss . '&u=https%253A%252F%252Ftieba.baidu.com%252Fp%252F4910301386%253Fpn%253D1&loginVersion=v4&qrcode=1&tpl=tb&apiver=v3&tt=1577607039318&traceid=&time=1577607039&alg=v3&callback=bd__cbs__n430h';

		$res = $http->setUrl($url)->setIsHeader(1)->http();

		if (preg_match_all('/Set-Cookie:([^=]+)=([^;]+)/', $res, $matchs)) {
			$cookie = '';
			foreach ($matchs[0] as $value) {
				$cookie .= str_replace('Set-Cookie:', '', $value) . ';';
			}
			die($cookie);

		}

	}
	die('bduss err');

}

?>