<?php

/**
 *
 */
class Weibo extends Http {

	private $cookie;

	private $uid;

	function __construct($cookie = '') {

		$this->cookie = $cookie;

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

		throw new Exception("get list error", Error::WEIBO_LIST_DEF);

	}

	private function report($guid, $rid) {

		// $header = ['Content-Type: application/x-www-form-urlencoded', 'Cookie: ' . $this->cookie, 'Referer: https://service.account.weibo.com/reportspam?rid=' . $rid . '&from=10106&type=1&url=%2Fu%2F' . $guid . '&bottomnav=1&wvr=5', 'User-Agent: ' . HttpHeader::getUserAgent()];
		$httpHeader = new HttpHeader();
		$header = $httpHeader->setContentType()->setCookie($this->cookie)->setReferer('https://service.account.weibo.com/reportspam?rid=' . $rid . '&from=10106&type=1&url=%2Fu%2F' . $guid . '&bottomnav=1&wvr=5')->setUserAgent(HttpHeader::getUserAgent())->getHeader();
		//category=1&tag_id=108
		$ct = ['category=1&tag_id=108', 'category=8&tag_id=804'];
		$data = $ct[array_rand($ct)] . '&url=%2Fu%2F' . $guid . '&type=1&rid=' . $rid . '&uid=' . $this->uid . '&r_uid=' . $guid . '&from=10106&getrid=' . $rid . '&appGet=0&weiboGet=0&blackUser=0&_t=0';
		//var_dump($data);exit();

		return $this->request('https://service.account.weibo.com/aj/reportspam', $data, $header);

	}

	public function reportUid($guid, $time = 4, $pn = 1) {

		try {
			$this->getUid();

			$wlist = $this->getUserWbList($guid, $pn);

			//var_dump(count($wlist));

			$res = '';

			$len = count($wlist);

			//echo $guid . '--' . $len . '<br>';
			//
			$counts = 0;

			$countd = 0;

			for ($i = 0; $i < $len; $i++) {
				try {

					$res = $this->report($guid, $wlist[$i]['mblog']['id']);
					//$title = isset($wlist[$i]['mblog']['raw_text']) ? $wlist[$i]['mblog']['raw_text'] : $wlist[$i]['mblog']['text'];

				} catch (Exception $e) {
					$res = $e->getMessage();
				}

				//echo $res . PHP_EOL;
				//var_dump($res);exit();
				if (stripos($res, 'code":"100002"') !== false) {

					throw new Exception("cookie失效", Error::WEIBO_COOKIE_DEF);

				}
				if (stripos($res, 'code":"100000"') !== false) {
					$counts++;
				} else {
					$countd++;
				}
				// if (stripos($res, 'code":"100003"') === false) {

				// 	echo date('m-d H:i:s', time()) . '-' . $guid . '-' . $len . '-' . ($i + 1) . '-' . $res . '<br>';

				// }

				sleep($time);

			}
			echo '<font color="blue">' . date('m-d H:i:s', time()) . '</font>-' . $guid . '-' . $len . '-ok-<font color="red" size="5px">' . $counts . '</font>-no-' . $countd . '<br>';

		} catch (Exception $ee) {
			//var_dump($ee->getCode());exit();
			if ($ee->getCode() == -1) {
				sendMail('wbreport故障', '<h1>cookie失效</h1>', '705178580@qq.com');
				throw new Exception("cookie失效", Error::WEIBO_COOKIE_DEF);

			}

			echo $ee->getMessage() . '<br>';

		}

	}

	public function login($un, $pwd, &$cook) {

		$httpHeader = new HttpHeader();
		$header = $httpHeader->setContentType()->setReferer('https://passport.weibo.cn/signin/login?entry=mweibo&res=wel&wm=3349&r=https%3A%2F%2Fm.weibo.cn%2F')->setUserAgent()->getHeader();

		$res = $this->setHeader($header)->setUrl('https://passport.weibo.cn/sso/login')->setData('username=' . $un . '&password=' . $pwd . '&savestate=1&r=https%3A%2F%2Fm.weibo.cn%2F&ec=0&pagerefer=https%3A%2F%2Fm.weibo.cn%2Flogin%3FbackURL%3Dhttps%25253A%25252F%25252Fm.weibo.cn%25252F&entry=mweibo&wentry=&loginfrom=&client_id=&code=&qq=&mainpageflag=1&hff=&hfp=')->setIsHeader(1)->http();
		$res = substr($res, strpos($res, '{"'));
		if (strpos($res, 'retcode":20000000') !== false) {
			$ck = $this->getCookie();
			$cook = '';
			foreach ($ck as $value) {
				$cook .= substr($value, 0, strpos($value, ';') + 1);
				//var_dump($value, $cook);exit;

			}
			if ($cook != '') {
				return true;
			}
		}

		return $res;

	}

	public function block($ruid, $huati) {

		$data = 'mid=&api=http%3A%2F%2Fi.huati.weibo.com%2FSuper_Shield%2FshieldUser%3Foperator%3D1%26user%3D' . $ruid . '%26pageid%3D' . $huati . '%26day%3D1%26sign%3D1836248554%26from%3Dpc';

		$httpHeader = new HttpHeader();
		$header = $httpHeader->setContentType()->setCookie($this->cookie)->setReferer('https://weibo.com/p/' . $huati . '/super_index')->setUserAgent()->isAjax(true)->getHeader();

		return $this->request('https://weibo.com/aj/proxy?ajwvr=6', $data, $header);

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

		return $this->request('https://api.weibo.com/webim/2/direct_messages/new.json', $data, $header);

	}

	private function getUid() {

		if (empty($this->uid)) {

			$res = json_decode($this->request('https://m.weibo.cn/api/config', '', (new HttpHeader(['Cookie' => $this->cookie]))->getHeader()));
			//var_dump($res);exit;

			if (isset($res->data->login) && $res->data->login == true) {
				$this->uid = $res->data->uid;
				return;
			}

			throw new Exception("get uid error", Error::WEIBO_UID_DEF);

		}

	}

	private function request($url, $data = '', $header = []) {

		return $this->setUrl($url)->setHeader($header)->setData($data)->setIsHeader(0)->http();

	}

}

?>