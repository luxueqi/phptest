<?php
if (!defined('EXITFORBID')) {
	exit('forbid');
}
function C($v) {
	$path = CONF_PATH . '/' . $v . '.php';
	if (is_file($path)) {
		return require $path;
	}

	return [];

}

function dump($val) {

	echo "<pre>";
	var_dump($val);
	echo "</pre>";
	exit;

}

/***
获取get or post 数据或者 指定key数据
m 不存在时的默认值
f 过滤 函数
 ***/
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

function sendMail($title, $content, $sendemail) {

	require_once LIB_PATH . '/vendor/mail/PHPMailer.php';

	require_once LIB_PATH . '/vendor/mail/SMTP.php';

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

function creatCaptcha() {
	require_once LIB_PATH . '/vendor/php-captcha/CaptchaBuilderInterface.php';

	require_once LIB_PATH . '/vendor/php-captcha/CaptchaBuilder.php';

	$captch = new CaptchaBuilder();

	$captch->initialize([
		'width' => 150, // 宽度
		'height' => 50, // 高度
		'line' => false, // 直线
		'curve' => true, // 曲线
		'noise' => 1, // 噪点背景
		'fonts' => [], // 字体
	]);

	$captch->create();
	$_SESSION['captcha'] = $captch->getText();
	$captch->output(1);

}

function checkCaptcha($captcha) {
	if (strtoupper(Session('captcha')) != strtoupper($captcha)) {
		exitMsg(ErrorConst::API_PARAMS_ERRNO, '验证码错误');
	}
	Session('captcha', null);
}

/**
 * [strMid description]
 * @param  [string]  $left  [截取文本的左边]
 * @param  [string]  $right [截取文本的右边]
 * @param  [string]  $str   [要输入的文本]
 * @param  boolean $pl    [是否批量]
 * @return [type]         [批量返回array 否则 string ]
 */
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

		$i = $r + strlen($right);

		//$start = $r;

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
/**
 * [randStr description]
 * @param  integer $len  []
 * @param  integer $type [0.数字,1.英文字母,2.英文数字]
 * @return [type]        [description]
 */
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

		return setcookie($param[0], $param[1], isset($param[2]) ? time() + $param[2] : 0, '/');
	}
	return false;
}

function qchche() {
	if (isset(C(__M__)['cache']) ? C(__M__)['cache'] : false) {
		echo '<a href="/' . __M__ . '-' . __C__ . '-' . __A__ . '.html?qflag=1">上次缓存时间【' . date('Y-m-d H:i:s', time()) . '】重新生成</a>';
	}

}

function Session(...$param) {
	$count = func_num_args();
	if ($count == 1) {
		return isset($_SESSION[$param[0]]) ? $_SESSION[$param[0]] : null;
	} elseif ($count == 2) {
		$_SESSION[$param[0]] = $param[1];
		return true;
	}
	return null;
}

function isGetPostAjax($m = 'get') {
	if ($m == 'get' || $m == 'post') {
		return strtoupper($_SERVER['REQUEST_METHOD']) == strtoupper($m);
	} elseif ($m == 'ajax') {
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtoupper($_SERVER['HTTP_X_REQUESTED_WITH']) == 'XMLHTTPREQUEST';
	}
	return false;
}

function strReplaceStart($strs, $lnum = 1, $rnum = 1, $rs = '*') {
	return mb_substr($strs, 0, $lnum, 'utf-8') . str_repeat($rs, 3) . mb_substr($strs, 0 - $rnum, $rnum, 'utf-8');
	//return preg_replace('/^(.{' . $lnum . '})(.*?)(.{' . $rnum . '})$/u', '$1' . str_repeat($rs, 3) . '$3', $strs);
}

//Remove the exploer'bug XSS.
function RemoveXSS($val) {

	$val = htmlspecialchars($val);
	// remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
	// this prevents some character re-spacing such as <java\0script>
	// note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
	$val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
	// straight replacements, the user should never need these since they're normal characters
	// this prevents like <IMG SRC=@avascript:alert('XSS')>
	$search = 'abcdefghijklmnopqrstuvwxyz';
	$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$search .= '1234567890!@#$%^&*()';
	$search .= '~`";:?+/={}[]-_|\'\\';
	for ($i = 0; $i < strlen($search); $i++) {
		// ;? matches the ;, which is optional
		// 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
		// @ @ search for the hex values
		$val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
		// @ @ 0{0,7} matches '0' zero to seven times
		$val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
	}
	// now the only remaining whitespace attacks are \t, \n, and \r
	$ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
	$ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
	$ra = array_merge($ra1, $ra2);
	$found = true; // keep replacing as long as the previous round replaced something
	while ($found == true) {
		$val_before = $val;
		for ($i = 0; $i < sizeof($ra); $i++) {
			$pattern = '/';
			for ($j = 0; $j < strlen($ra[$i]); $j++) {
				if ($j > 0) {
					$pattern .= '(';
					$pattern .= '(&#[xX]0{0,8}([9ab]);)';
					$pattern .= '|';
					$pattern .= '|(&#0{0,8}([9|10|13]);)';
					$pattern .= ')*';
				}
				$pattern .= $ra[$i][$j];
			}
			$pattern .= '/i';
			$replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2); // add in <> to nerf the tag
			$val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
			if ($val_before == $val) {
				// no replacements were made, so exit the loop
				$found = false;
			}
		}
	}
	return $val;
}

//防注入
function abacaAddslashes($var) {
	if (!get_magic_quotes_gpc()) {
		if (is_array($var)) {
			foreach ($var as $key => $val) {
				$var[$key] = abacaAddslashes($val);
			}
		} else {
			$var = addslashes($var);
		}
	}
	return $var;
}

?>