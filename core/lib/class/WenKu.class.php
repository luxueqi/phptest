<?php

class WenKu {

	public static function sign($cookie) {
		$header = ['Cookie: ' . $cookie, 'Referer: https://wenku.baidu.com/task/browse/daily', 'X-Requested-With: XMLHttpRequest'];

		$http = new Http('https://wenku.baidu.com/task/submit/signin', $header);

		return json_decode($http->http(), true);

	}

}

?>