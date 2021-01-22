<?php
if (!defined('EXITFORBID')) {
	exit('forbid');
}

session_name('wsign');
session_start();

Middle::Mid('get', '/wsign/tsign/cron', function () {

	$redis = Mredis::getInstance();

	//$lasttime = $redis->getVal('cronlasttime');

	//$curtime = time();

	if ($redis->exists('cronlasttime')) {
		die('频繁');
	}

	$redis->setVal('cronlasttime', true, 59);

})::Mid('get|post', '/wsign/(admin|info)/[a-z]+', function () {

	WsignBase::needLoginS('/wsign-login-login.html');

});

?>