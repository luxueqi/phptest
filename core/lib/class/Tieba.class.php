<?php
/**
 *
 */
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class Tieba extends Http {

	private $data = [];

	private $bduss;

	private $tbs;

	private function initCommonData() {

		$timestamp = intval(microtime(true) * 1000);

		$this->data = [
			'_client_id' => 'wappc_1559714929207_884',
			'_client_type' => 1,
			'_client_version' => '10.2.4',
			'_os_version' => '12.3.1',
			'_phone_imei' => 'AE2CBC629C2745EFE8129FB3DCE46101',
			'_phone_newimei' => 'AE2CBC629C2745EFE8129FB3DCE46101',
			'_timestamp' => $timestamp,
			'brand' => 'iPhone',
			'brand_type' => 'iPhone 6S',
			'cuid' => 'AE2CBC629C2745EFE8129FB3DCE46101',
			'from' => 'appstore',
			'lego_lib_version' => '3.0.0',
			'm_cost' => 585.227013,
			'm_logid' => 3052876179,
			'm_size_d' => 283,
			'm_size_u' => 4878,
			'model' => 'iPhone 6S',
			'net_type' => 1,
			'shoubai_cuid' => 'DFAF30D54C2C457186B6BD4D020692170BCAB0F94FHEPEGIILD',
			'stoken' => '',
			'subapp_type' => 'tieba',
			'z_id' => 'YdGh_vJLQoNLhh02Rba1OKOYx65J2INcoAdqvO4mhWcwuxm2HgH5QrU7GKk6-NF2NQsFS5TOIpFTo-cNOiz8ktg',

		];

	}

	function __construct($BDUSS = '') {
		//$this->initCommonData();
		if (!empty($BDUSS)) {

			$this->bduss = $BDUSS;
		}

	}

	private function setDatac($k = '', $v = '') {

		if (is_array($k)) {

			$this->data = array_merge($this->data, $k);

		} elseif ($k == '' && $v == '') {

			$this->data = '';

		} else {
			$this->data[$k] = $v;
		}

		return $this;
	}

	private function uid2portrait($uid) {
		$strc = str_pad(dechex(trim($uid)), 8, 0, STR_PAD_LEFT);
		$sc = '';
		for ($i = 6; $i >= 0; $i -= 2) {
			$sc .= substr($strc, $i, 2);
		}

		return $sc;
	}

	public function portrait2uid($portrait) {
		$portrait = substr($portrait, 0, 4) . substr($portrait, -4, 4);
		//var_dump(substr($portrait, 0, 4), substr($portrait, -4, 4));
		$sc = '';
		for ($i = 6; $i >= 0; $i -= 2) {
			$sc .= substr($portrait, $i, 2);
		}
		return hexdec($sc);
	}

	public function name2uid($un) {
		//http://tieba.baidu.com/home/get/panel?ie=utf-8&un
		$res = @json_decode($this->request('http://tieba.baidu.com/home/get/panel?ie=utf-8&un=' . $un), true);
		//var_dump($res['data']['id']);exit;
		return isset($res['data']['id']) ? $res['data']['id'] : '';
	}

	private function md5sign() {
		$tdata = '';
		$datac = '';
		ksort($this->data);
		//var_dump($this->data);exit;
		foreach ($this->data as $key => $value) {
			$tdata .= $key . '=' . $value;
			$datac .= $key . '=' . $value . '&';
		}
		$this->data = $datac . 'sign=' . md5($tdata . 'tiebaclient!!!');

		return $this;

	}

	public function getFid($kw) {

		$res = json_decode($this->request(TiebaConst::HTTP_URL . '/f/commit/share/fnameShareApi?ie=utf-8&fname=' . $kw));

		if (isset($res->data->fid)) {
			return $res->data->fid;
		}

		throw new Exception("get fid error");

	}

	public function getTbs() {

		$res = json_decode($this->request(TiebaConst::HTTP_URL . '/dc/common/tbs', '', ['Cookie: BDUSS=' . $this->bduss]));

		if (isset($res->is_login) && $res->is_login == 1) {
			return $res->tbs;
		}

		throw new Exception("get tbs error");
	}
/**
 * [block description]
 * @param  [string] $word  [贴吧名]
 * @param  [string] $value [对应type 值]
 * @param  [string] $type  [封禁类型un uid portrait]
 * @return [type]        [description]
 */
	public function block($word, $value, $type) {

		$this->initCommonData();

		$portrait = '';

		$un = '';

		if ($type == 'uid') {
			$portrait = $this->uid2portrait($value);
		} elseif ($type == 'portrait') {
			$portrait = $value;
		} else {
			$un = $value;
		}
		$this->tbs = $this->getTbs();

		$tdata = ['BDUSS' => $this->bduss, 'tbs' => $this->tbs, 'z' => '6233732579', 'day' => 1, 'word' => $word, 'nick_name' => '', 'portrait' => $portrait, 'm_api' => 'c/u/bawu/listreason', 'ntn' => 'banid', 'reason' => 'test', 'post_id' => '6233732579', 'un' => $un, 'fid' => $this->getFid($word)];

		return $this->setDatac($tdata)->md5sign()->request(TiebaConst::APP_URL . '/c/c/bawu/commitprison', $this->data);

	}

	static public function blockStatic($word, $fid, $bduss, $tbs, $type, $value) {
		$that = new self();
		$that->initCommonData();
		$portrait = '';
		$un = '';
		if ($type == 1) {
			$portrait = $that->uid2portrait($value);
		} else {
			$un = $value;
		}

		$tdata = ['BDUSS' => $bduss, 'tbs' => $tbs, 'z' => '6233732579', 'day' => 1, 'word' => $word, 'nick_name' => '', 'portrait' => $portrait, 'm_api' => 'c/u/bawu/listreason', 'ntn' => 'banid', 'reason' => 'test', 'post_id' => '6233732579', 'un' => $un, 'fid' => $fid];
		return json_decode($that->setDatac($tdata)->md5sign()->request(TiebaConst::APP_URL . '/c/c/bawu/commitprison', $that->data), true);
	}

	static public function topStatic($word, $fid, $bduss, $tbs, $tid) {
		$that = new self();
		$that->initCommonData();
		$tdata = ['BDUSS' => $bduss, 'tbs' => $tbs, 'z' => $tid, 'word' => $word, 'm_api' => 'c/s/newlog', 'ntn' => 'set', 'fid' => $fid];
		return json_decode($that->setDatac($tdata)->md5sign()->request(TiebaConst::APP_URL . '/c/c/bawu/committop', $that->data), true);
	}

	static public function topendTime($fid, $bduss, $tbs) {
		$that = new self();
		return json_decode($that->request("https://tieba.baidu.com/mo/q/bawu/taskInfo?fid={$fid}&tbs={$tbs}", '', ["Cookie: BDUSS={$bduss}"]), true);

	}

	static public function u2p($uid) {
		return (new self())->uid2portrait($uid);
	}

	public function getUidName() {
		$res = @json_decode($this->request('http://help.baidu.com/api/count', '', ['Cookie: BDUSS=' . $this->bduss]));
		if (!isset($res->uid)) {
			throw new Exception("get uidname error");

		}
		return ['name' => $res->uname, 'uid' => $res->uid];
	}

	public function like($pn = 1, $uid = '', $count = 60) {
		if ($uid == '') {
			$uid = $this->getUidName()['uid'];
		}
		$this->initCommonData();
		$tdata = ['BDUSS' => $this->bduss, 'uid' => $uid, 'page_size' => $count, 'page_no' => $pn];
		//var_dump(json_decode($this->setDatac($tdata)->md5sign()->request(TiebaConst::APP_URL . '/c/f/forum/like', $this->data), true));exit;
		$list = json_decode($this->setDatac($tdata)->md5sign()->request(TiebaConst::APP_URL . '/c/f/forum/like', $this->data), true)['forum_list'];

		$c = count($list);
		//var_dump($list, $c);exit;
		if ($c) {
			//array_push($list['non-gconforum'], $list['gconforum']);
			return array_merge($list['non-gconforum'], isset($list['gconforum']) ? $list['gconforum'] : []);
		}

		return [];
	}

	public function sign($bduss, $tbs, $fid, $uid, $kw) {
		$this->initCommonData();
		$tdata = ['BDUSS' => $bduss, 'tbs' => $tbs, 'fid' => $fid, 'kw' => $kw, 'uid' => $uid];
		$res = json_decode($this->setDatac($tdata)->md5sign()->request(TiebaConst::APP_URL . '/c/c/forum/sign', $this->data . '&sig=9b7c9d4ac0772d6d96a989e9651119b9'), true);
		return $res;
	}

}

?>