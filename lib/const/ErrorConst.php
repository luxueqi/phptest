<?php

/**
 *lll
 */
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class ErrorConst {

	//public function __construct() {echo "string";}

	const API_ERRNO = 0; //接口错误

	const HTTP_CODE = 1001; //httpclass 请求错误

	const WEIBO_COOKIE_DEF = -1; //weibo.cookie 失效

	const WEIBO_LIST_DEF = 101; //weibo.list 获取失败

	const WEIBO_UID_DEF = 102; //weibo.uid获取失败

	const VALIDATE_ERRNO = 2001; //规则ID错误

};
//var_dump(ErrorConst::HTTP_CODE);
?>