<?php
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class WeiboConst {
	const LOGIN_URL = 'https://passport.weibo.cn/sso/login';

	const BLOCK_URL = 'https://weibo.com/aj/proxy?ajwvr=6';

	const CONFIG_URL = 'https://m.weibo.cn/api/config';

	const REPORT_URL = 'https://service.account.weibo.com/aj/reportspam';

	const SENDMSG_URL = 'https://api.weibo.com/webim/2/direct_messages/new.json';
}

?>