<?php

require './init.php';

if (PHP_SAPI != 'cli') {
	set_time_limit(0);
	ob_end_clean();
	ob_implicit_flush();
	header('X-Accel-Buffering: no'); // 关键是加了这一行。

}
////
$cookie = C('weibo')['cookie'];
while (1) {
	$weibo = new Weibo($cookie);
	$weibo->reportUid(3858588815);
	$weibo->reportUid(6995572700);
	$weibo->reportUid(6352407779);
	$weibo->reportUid(6065228163);
	sleep(mt_rand(2000, 5600));
}

?>