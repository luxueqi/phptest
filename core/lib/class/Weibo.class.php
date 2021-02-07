<?php

/**
 *
 */
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class Weibo extends Http {

	private $cookie;

	private $uid;

	function __construct($cookie = '') {

		$this->cookie = $cookie;

	}

	public function dayGy($cookie) {
		$res = [];
		$h = new HttpHeader();
		$postdata = ['aj_mblog_addmblog' => 'action=1&pid=&preview=false&gid=4&type=&shortURL=&task=repost&content=%23%E6%AF%8F%E6%97%A5%E4%B8%80%E5%96%84%23%20%20%E5%85%B3%E6%B3%A8%E4%BB%96%E4%BB%AC%EF%BC%8C%E5%B8%AE%E5%8A%A9%E4%BB%96%E4%BB%AC%EF%BC%8C%E4%B8%80%E6%AC%A1%E8%BD%AC%E5%8F%91%E8%83%BD%E8%AE%A9%E6%9B%B4%E5%A4%9A%E4%BA%BA%E8%A1%8C%E5%8A%A8%E8%B5%B7%E6%9D%A5%EF%BC%81&appkey=&style_type=1&location=partner&module=shissue&_t=0'];
		$header = $h->setContentType()->setCookie($cookie)->setReferer('https://gongyi.weibo.com/2150961184/profile')->setUserAgent('Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Mobile Safari/537.36')->isAjax(true)->getHeader();
		foreach ($postdata as $key => $value) {
			//防止频繁
			// if ($key == 'aj_mblog_addmblog') {
			// 	sleep(3);
			// }
			$res[] = json_decode($this->request('https://gongyi.weibo.com/' . $key, $value, $header), true);
		}

		return $res;

	}

	public function getHuatiList($huati, &$since_id = '') {
		$url = 'https://m.weibo.cn/api/container/getIndex?extparam=%E9%99%86%E9%9B%AA%E7%90%AA&containerid=100808' . md5($huati) . '_-_feed&luicode=20000174&since_id=' . $since_id;

		$res = json_decode($this->request($url), true);

		//dump($res);

		if (isset($res['data']['cards'])) {

			$listw = $res['data']['cards'];

			//$index = 1;
			//
			//dump($listw);

			foreach ($listw as $key => $value) {

				if (isset($value['show_type']) && $value['show_type'] == 1) {
					$since_id = $res['data']['pageInfo']['since_id'];
					return $value['card_group'];
				}
			}

			//return var_dump(array_slice($listw, $index));

		}

	}

	public function getUserWbList($guid, $pn = 1) {

		$url = 'https://m.weibo.cn/api/container/getIndex?containerid=230413' . $guid . '_-_WEIBO_SECOND_PROFILE_WEIBO&page_type=03&page=' . $pn;

		$res = json_decode($this->request($url), true);

		if (isset($res['data']['cards'])) {

			$listw = $res['data']['cards'];

			$index = 1;

			foreach ($listw as $key => $value) {
				if ($value['card_type'] == 9) {
					$index = $key;
					break;
				}
			}

			return array_slice($listw, $index);

		}

		throw new Exception("get list error", ErrorConst::WEIBO_LIST_DEF);

	}

	private function report($guid, $rid, $ref = '') {

		// $header = ['Content-Type: application/x-www-form-urlencoded', 'Cookie: ' . $this->cookie, 'Referer: https://service.account.weibo.com/reportspam?rid=' . $rid . '&from=10106&type=1&url=%2Fu%2F' . $guid . '&bottomnav=1&wvr=5', 'User-Agent: ' . HttpHeader::getUserAgent()];
		if (!$ref) {
			$ref = '%2Fu%2F' . $guid;
		}
		$url = 'https://service.account.weibo.com/reportspam?rid=' . $rid . '&from=19999&type=1&url=' . $ref . '&bottomnav=1&wvr=5';

		$res = $this->setIsHeader(1)->setHeader(['Cookie: ' . $this->cookie])->setUrl($url)->setGet()->http();

		//var_dump($res);

		$ck = $this->getCookie('24efc99cc47fe52bf03caf14fc057836beb90f48');

		$cktmp = $this->cookie;

		if ($ck != '') {
			$cktmp = $this->cookie . ';24efc99cc47fe52bf03caf14fc057836beb90f48=' . $ck;
		} else {
			throw new Exception("get report key err", -5);

		}

		//dump(isset($ck[0]), strpos(ltrim($ck[0]), '24efc99cc47fe52bf03caf14fc057836beb90f48') == 0, $ck);

		// if (isset($ck[0]) && strpos(ltrim($ck[0]), '24efc99cc47fe52bf03caf14fc057836beb90f48') === 0) {
		// 	$cktmp = $this->cookie . ';' . $ck[0];

		// } else {
		// 	throw new Exception("get report key err", -5);

		// }

		//echo $cktmp;
		//dump($ck);

		$httpHeader = new HttpHeader();
		$header = $httpHeader->setContentType()->setCookie($cktmp)->setReferer($url)->setUserAgent(HttpHeader::getUserAgent())->getHeader();
		//category=1&tag_id=108
		$ct = ['category=8&tag_id=804', 'category=2&tag_id=202'];
		$data = $ct[array_rand($ct)] . '&url=%2Fu%2F' . $guid . '&type=1&rid=' . $rid . '&uid=' . $this->uid . '&r_uid=' . $guid . '&from=99&getrid=' . $rid . '&appGet=0&weiboGet=0&blackUser=1&_t=0';
		//var_dump($data);exit();
		//dump($header);
		return $this->request(WeiboConst::REPORT_URL, $data, $header);

	}

	private function reportList($wlist, $blackarr = [], $ref = '') {
		$restmp = '';

		$len = count($wlist);

		//echo $guid . '--' . $len . '<br>';
		//
		// $counts = 0;

		// $countd = 0;

		$counth = 0;

		for ($i = 0; $i < $len; $i++) {
			try {

				$guid = $wlist[$i]['mblog']['user']['id'];

				//echo $wlist[$i]['mblog']['user']['id'] . '--' . $wlist[$i]['mblog']['id'] . PHP_EOL;
				if (!empty($blackarr)) {
					if (!in_array($guid, $blackarr)) {
						$counth++;
						continue;
					}
				}
				$rid = $wlist[$i]['mblog']['id'];
				$res = json_decode($this->report($guid, $rid, $ref), true);

				$restmp = isset($res['msg']) ? $res['msg'] : 'json err';

				//$title = isset($wlist[$i]['mblog']['raw_text']) ? $wlist[$i]['mblog']['raw_text'] : $wlist[$i]['mblog']['text'];

			} catch (Exception $e) {
				$restmp = $e->getMessage();
			}
			echo ($i + 1) . '.' . $rid . '-' . $restmp . PHP_EOL;
			//dump($res);
			//echo $res . PHP_EOL;
			//var_dump($res);exit();
			// if (stripos($res, 'code":"100002"') !== false) {

			// 	throw new Exception("cookie失效", Error::WEIBO_COOKIE_DEF);

			// }
			// if (stripos($res, 'code":"100000"') !== false) {
			// 	$counts++;
			// } else {
			// 	$countd++;
			// }
			// if (stripos($res, 'code":"100003"') === false) {

			// 	echo date('m-d H:i:s', time()) . '-' . $guid . '-' . $len . '-' . ($i + 1) . '-' . $res . '<br>';

			// }
			//echo date('m-d H:i:s', time()) . '-' . $len . '-ok-' . $counts . '-no-' . $countd . '-bh-' . $counth . '<br>';
			sleep(4);

		}
		//echo '<font color="blue">' . date('m-d H:i:s', time()) . '</font>-' . $len . '-ok-<font color="red" size="5px">' . $counts . '</font>-no-' . $countd . '-bh-' . $counth . '<br>';
	}

	public function reportUid($guid, $pn = 1) {

		try {
			$this->getUid();

			$wlist = $this->getUserWbList($guid, $pn);

			$this->reportList($wlist);

		} catch (Exception $ee) {
			//var_dump($ee->getCode());exit();
			if ($ee->getCode() == -1) {
				sendMail('wbreport故障', '<h1>cookie失效</h1>', '705178580@qq.com');
				throw new Exception("cookie失效", ErrorConst::WEIBO_COOKIE_DEF);

			}

			echo $ee->getMessage() . '<br>';

		}

	}

	public function reportHuati($huati, $since_id = 1, $blackarr = []) {
		try {
			$this->getUid();

			$wlist = [];
			$sid = '';
			for ($i = 0; $i < $since_id; $i++) {
				$wlist = array_merge($wlist, $this->getHuatiList($huati, $sid));
			}
			//dump($wlist);
			$this->reportList($wlist, $blackarr, '%2Fp%2Faj%2Fv6%2Fmblog%2Fmbloglist');

		} catch (Exception $ee) {
			//var_dump($ee->getCode());exit();
			if ($ee->getCode() == -1) {
				sendMail('wbreport故障', '<h1>cookie失效</h1>', '705178580@qq.com');
				throw new Exception("cookie失效", ErrorConst::WEIBO_COOKIE_DEF);

			}

			echo $ee->getMessage() . '<br>';

		}
	}

	public function login($un, $pwd, &$cook) {

		$httpHeader = new HttpHeader();
		$header = $httpHeader->setContentType()->setReferer('https://passport.weibo.cn/signin/login?entry=mweibo&res=wel&wm=3349&r=https%3A%2F%2Fm.weibo.cn%2F')->setUserAgent()->getHeader();

		$res = $this->setHeader($header)->setUrl(WeiboConst::LOGIN_URL)->setData('username=' . $un . '&password=' . $pwd . '&savestate=1&r=https%3A%2F%2Fm.weibo.cn%2F&ec=0&pagerefer=https%3A%2F%2Fm.weibo.cn%2Flogin%3FbackURL%3Dhttps%25253A%25252F%25252Fm.weibo.cn%25252F&entry=mweibo&wentry=&loginfrom=&client_id=&code=&qq=&mainpageflag=1&hff=&hfp=')->setIsHeader(1)->http();
		$res = substr($res, strpos($res, '{"'));
		if (strpos($res, 'retcode":20000000') !== false) {
			$ck = $this->getCookie();
			$cook = '';
			foreach ($ck as $value) {
				$cook .= substr($value, 0, strpos($value, ';') + 1);
				//var_dump($value, $cook);exit;

			}
			$cook = trim($cook);
			if ($cook != '') {
				return true;
			}
		}

		return $res;

	}

	public function block($ruid, $huati, $day = 1) {

		$data = 'mid=&api=http%3A%2F%2Fi.huati.weibo.com%2FSuper_Shield%2FshieldUser%3Foperator%3D1%26user%3D' . $ruid . '%26pageid%3D' . $huati . '%26day%3D' . $day . '%26sign%3D1836248554%26from%3Dpc';

		$httpHeader = new HttpHeader();
		$header = $httpHeader->setContentType()->setCookie($this->cookie)->setReferer('https://weibo.com/p/' . $huati . '/super_index')->setUserAgent()->isAjax(true)->getHeader();

		return $this->request(WeiboConst::BLOCK_URL, $data, $header);

	}

	public function sign($htid, $cookie) {

		$url = 'https://weibo.com/p/aj/general/button?ajwvr=6&api=http://i.huati.weibo.com/aj/super/checkin&texta=%E7%AD%BE%E5%88%B0&textb=%E5%B7%B2%E7%AD%BE%E5%88%B0&status=0&id=' . $htid . '&location=page_100808_super_index&timezone=GMT+0800&lang=zh-cn&plat=MacIntel&ua=Mozilla/5.0%20(Macintosh;%20Intel%20Mac%20OS%20X%2010_13_5)%20AppleWebKit/537.36%20(KHTML,%20like%20Gecko)%20Chrome/76.0.3809.132%20Safari/537.36&screen=1440*900&__rnd=' . number_format(microtime(true), 3, '', '');

		$httpHeader = new HttpHeader();
		$header = $httpHeader->setContentType()->setCookie($cookie)->setReferer('https://weibo.com/p/' . $htid . '/super_index')->setUserAgent()->isAjax(true)->getHeader();
		return $this->request($url, '', $header);

	}

	public function sendMsg($suid, $content) {

		$data = 'text=' . $content . '&uid=' . $suid . '&extensions=%7B%7D&is_encoded=0&decodetime=1&source=209678993';

		$httpHeader = new HttpHeader();

		$header = $httpHeader->setContentType()->setCookie($this->cookie)->setReferer('https://api.weibo.com/chat/')->setUserAgent()->getHeader();

		return $this->request(WeiboConst::SENDMSG_URL, $data, $header);

	}

	public function getUid() {

		if (empty($this->uid)) {

			$res = json_decode($this->request(WeiboConst::CONFIG_URL, '', (new HttpHeader(['Cookie' => $this->cookie]))->getHeader()));
			//var_dump($res);exit;

			if (isset($res->data->login) && $res->data->login == true) {
				$this->uid = $res->data->uid;
				return;
			}

			throw new Exception("get uid error", ErrorConst::WEIBO_UID_DEF);

		}

	}

	public function getUidName() {

		$re = @json_decode($this->request('https://m.weibo.cn/profile/info', '', (new HttpHeader(['Cookie' => $this->cookie]))->getHeader()), true);
		$uidname = array();
		if (!empty($re['data'])) {
			$uidname['id'] = $re['data']['user']['id'];
			$uidname['name'] = $re['data']['user']['screen_name'];
		}
		return $uidname;
	}

}

?>