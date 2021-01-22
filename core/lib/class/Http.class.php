<?php

/**
 *
 */
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class Http {

	private $url;
	private $data;
	private $ip;
	private $header;
	private $is_header = 0;
	private $cookiec;

	function __construct($url = '', $header = [], $data = '') {

		$this->url = $url;

		$this->data = $data;

		$this->header = $header;
	}

	public function initClear() {
		$this->url = '';
		$this->data = '';
		$this->ip = '';
		$this->header = [];
		$this->is_header = 0;
		$this->cookiec = '';
		return $this;
	}

	public function getCookie($key = '') {
		$res = '';
		if (!empty($key)) {
			foreach ($this->cookiec as $v) {
				$v = trim($v);
				if (strpos($v, $key) === 0) {
					$endindex = strpos($v, ';');
					$startindex = strlen($key) + 1;
					$res = substr($v, $startindex, $endindex - $startindex);
					break;
				}
			}
		} else {
			$res = $this->cookiec;
		}

		return $res;
	}
	public function setIsHeader($v = 0) {
		$this->is_header = $v;

		return $this;
	}

	public function setUrl($url) {
		$this->url = $url;

		return $this;
	}

	public function setHeader($header = []) {
		$this->header = $header;

		return $this;
	}

	public function setData($data = '') {
		$this->data = $data;
		return $this;
	}

	public function setIp($ip) {
		$this->ip = $ip;
		return $this;

	}
	public function setGet() {
		$this->data = '';
		return $this;

	}

	public function request($url, $data = '', $header = []) {

		return $this->setUrl($url)->setHeader($header)->setData($data)->setIsHeader(0)->http();

	}
	public function http() {

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $this->url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 抓取结果直接返回（如果为0，则直接输出内容到页面）
		curl_setopt($curl, CURLOPT_HEADER, $this->is_header);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);

		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		if (!empty($this->data)) {
			if (is_array($this->data)) {
				$this->data = http_build_query($this->data);
			}
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $this->data);
			//var_dump($this->data);die;
		}
		if (!empty($this->ip)) {
			$iparr = explode(':', $this->ip);
			curl_setopt($curl, CURLOPT_PROXY, $iparr[0]);

			curl_setopt($curl, CURLOPT_PROXYPORT, $iparr[1]);

		}
		$content = curl_exec($curl); //执行并存储结果

		if ($content === false) {

			throw new Exception(curl_error($curl), ErrorConst::HTTP_CODE);
		}
		if ($this->is_header && preg_match_all('/Set-Cookie:([^\n]+)/', $content, $matchs)) {
			//var_dump($matchs);exit();
			$this->cookiec = $matchs[1];
		}
		curl_close($curl);

		return $content;

	}
}

?>