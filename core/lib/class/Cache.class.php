<?php
if (!defined('EXITFORBID')) {
	exit('forbid');
}
class Cache {

	static private $conf = ['filename' => __M__ . '-' . __C__ . '-' . __A__ . '.html', 'time' => 1800, 'qflag' => false];

	static private $flag = true;

	static public function read($conf = []) {
		self::$conf = array_merge(self::$conf, $conf);

		//var_dump(self::$conf);
		ob_start();

		$cc = '';

		if (defined('SAE_ACCESSKEY')) {

			self::$conf['filename'] = 'saekv://' . self::$conf['filename'];
			//sae 利用kv 无法通过函数filemtime判定文件修改时间  本地静态化不考虑memcached redis
			//

			self::$flag = !self::$conf['qflag'] && file_exists(self::$conf['filename']);

			if (self::$flag) {
				$cc = file_get_contents(self::$conf['filename']);

				self::$flag = preg_match('/上次缓存时间【(.*)】重新生成/', $cc, $mc) && time() - strtotime($mc[1]) < self::$conf['time'];

			}

		} else {
			self::$conf['filename'] = CACHE_PATH . '/' . self::$conf['filename'];

			//var_dump(time() - filemtime(self::$conf['filename']), self::$conf['time']);

			self::$flag = !self::$conf['qflag'] && file_exists(self::$conf['filename']) && time() - filemtime(self::$conf['filename']) < self::$conf['time'];

		}

		if (self::$flag) {
			if (defined('SAE_ACCESSKEY')) {
				echo $cc;
			} else {
				include self::$conf['filename'];
			}
			exit;
		}

	}

	static public function write() {
		if (!self::$flag) {

			file_put_contents(self::$conf['filename'], ob_get_flush());
		}

	}

}

;?>