<?php
if (!defined('EXITFORBID')) {
	exit('forbid');
}
require_once LIB_PATH . '/vendor/wb/saetv2.ex.class.php';

interface OuthInterface {
	function getCodeUrl();
	function getToken();
}
class WbOuth implements OuthInterface {
	function getCodeUrl() {
		$conf = C('outh')['wb'];

		$o = new SaeTOAuthV2($conf['WB_AKEY'], $conf['WB_SKEY']);

		return $o->getAuthorizeURL($conf['WB_CALLBACK_URL']) . '&forcelogin=true';

	}

	function getToken() {
		$token = '';
		if (G('code') != '') {
			$conf = C('outh')['wb'];
			$o = new SaeTOAuthV2($conf['WB_AKEY'], $conf['WB_SKEY']);
			$keys = ['code' => G('code'), 'redirect_uri' => $conf['WB_CALLBACK_URL']];
			$token = $o->getAccessToken('code', $keys);

			$uid = isset($token['uid']) ? $token['uid'] : '';

		}
		return $token;

	}
}

class OuthMy {

	private static $staticclass = [];

	static function create($classname) {
		if (!isset(self::$staticclass[$classname])) {
			self::$staticclass[$classname] = new $classname();
		}
		return self::$staticclass[$classname];
	}

	/*
static function delToken($atoken) {
//
$conf = C('outh')['wb'];

$o = new SaeTOAuthV2($conf['WB_AKEY'], $conf['WB_SKEY']);

$o->oAuthRequest('https://api.weibo.com/oauth2/revokeoauth2', 'POST', ['access_token' => $atoken]);

}*/

}

?>