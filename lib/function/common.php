<?php

function C($v) {

	return require './conf/' . $v . '.php';

}

function G($k, $m = '', $f = '') {

	$data = ['post.' => $_POST, 'get.' => $_GET, 'k' => isset($_REQUEST[$k]) ? $_REQUEST[$k] : $m];
	$returnArr = false;
	if ($k == 'post.' || $k == 'get.') {
		$returnArr = true;
		$data = $data[$k];
	} else {
		$data = ['k' => $data['k']];
	}

	if ($f != '' && function_exists($f)) {
		// var_dump($_REQUEST,$data);
		foreach ($data as $k => $v) {
			$data[$k] = $f($v);
		}
	}

	return $returnArr ? $data : $data['k'];
}

function Run() {

	if (isset($_SERVER['REDIRECT_SCRIPT_URL']) && stripos($_SERVER['REDIRECT_SCRIPT_URL'], '/api/') == 0) {
		$arr = explode('/', $_SERVER['REDIRECT_SCRIPT_URL'], 4);
		unset($arr[0]);
		require "./{$arr[1]}/{$arr[2]}/Api.php";
		(new Api)->$arr[3]();
	} else {
		echo "index";
	}
}

function sendMail($title, $content, $sendemail) {

	require_once './lib/vendor/mail/PHPMailer.php';

	require_once './lib/vendor/mail/SMTP.php';

	$conf = C('email');

	// 实例化PHPMailer核心类
	$mail = new PHPMailer();
// 是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
	//$mail->SMTPDebug = 1;lxjkywsgraqxbfid.
	// 使用smtp鉴权方式发送邮件
	$mail->isSMTP();
// smtp需要鉴权 这个必须是true
	$mail->SMTPAuth = true;
// 链接qq域名邮箱的服务器地址
	$mail->Host = 'smtp.qq.com';
// 设置使用ssl加密方式登录鉴权
	$mail->SMTPSecure = 'ssl';
// 设置ssl连接smtp服务器的远程服务器端口号
	$mail->Port = 465;
// 设置发送的邮件的编码
	$mail->CharSet = 'UTF-8';
// 设置发件人昵称 显示在收件人邮件的发件人邮箱地址前的发件人姓名
	$mail->FromName = 'xx';
// smtp登录的账号 QQ邮箱即可
	$mail->Username = $conf['username'];
// smtp登录的密码 使用生成的授权码
	$mail->Password = $conf['password'];
// 设置发件人邮箱地址 同登录账号
	$mail->From = $conf['username'];
// 邮件正文是否为html编码 注意此处是一个方法
	$mail->isHTML(true);
// 设置收件人邮箱地址
	$mail->addAddress($sendemail);
// 添加多个收件人 则多次调用方法即可
	//$mail->addAddress('87654321@163.com');
	// 添加该邮件的主题
	$mail->Subject = $title;
// 添加邮件正文
	$mail->Body = $content;
// 为该邮件添加附件
	//$mail->addAttachment('./example.pdf');
	// 发送邮件 返回状态
	return $mail->send();

	//var_dump($status);

}

function strMid($left, $right, $str, $pl = false) {

	$i = 0;

	$rstr = [];

	$strlen = strlen($str);

	while ($i < $strlen && ($l = strpos($str, $left, $i)) !== false) {

		$l = $l + strlen($left);

		$r = strpos($str, $right, $l);

		if ($r !== false) {
			array_push($rstr, substr($str, $l, $r - $l));

		}

		$i = $i + $r + strlen($right);

		if (!$pl) {
			break;
		}

	}
	if (empty($rstr)) {
		//throw new Exception("没有截取到字符串");
		$rstr = [''];
	}

	return $pl ? $rstr : $rstr[0];

}

function randStr($len = 4, $type = 0) {
	# code...
	$strsarr = [
		'0123456789',
		'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
		'0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
	];
	$strs = isset($strsarr[$type]) ? $strsarr[$type] : $strsarr[0];

	$lens = strlen($strs) - 1;

	$str = '';

	for ($i = 0; $i < $len; $i++) {
		$str .= $strs[mt_rand(0, $lens)];

	}

	return $str;

}
function exitMsg($code, $msg, $data = []) {
	echo json_encode(['code' => $code, 'msg' => $msg, 'data' => $data]);
	exit;
}

function Cookie(...$param) {
	$count = func_num_args();
	if ($count == 1) {
		return isset($_COOKIE[$param[0]]) ? $_COOKIE[$param[0]] : false;
	} elseif ($count == 3 || $count == 2) {

		return setcookie($param[0], $param[1], isset($param[2]) ? time() + $param[2] : 0);
	}
	return false;
}

function Session(...$param) {
	$count = func_num_args();
	if ($count == 1) {
		return isset($_SESSION[$param[0]]) ? $_SESSION[$param[0]] : false;
	} elseif ($count == 2) {
		$_SESSION[$param[0]] = $param[1];
		return true;
	}
	return false;
}

?>