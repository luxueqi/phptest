<?php

require_once './init.php';

$http = new Http();

$url = 'https://tieba.baidu.com/mo/q/postreport?pid=';

if (!isset($argv) || count($argv) != 3 || $argv[1] > $argv[2]) {
	die('param error' . PHP_EOL);
}
$start_time = time();
$count = $argv[2] - $argv[1] + 1;
echo "start:" . PHP_EOL . "worker:$count" . PHP_EOL . $argv[1] . '-' . $argv[2] . PHP_EOL;
$index = 0;
$js = [];
for ($i = $argv[1]; $i <= $argv[2]; $i++) {

	try {
		$res = $http->request($url . $i);
		$starti = 0;
		$portrait = strMid('/sys/portrait/item/', '.jpg', $res, false, $starti);
		$username = strMid('<span class="name">', '</span>', $res, false, $starti);
		//var_dump($starti);exit();
		$word = strMid('<span class="word">', '吧</span>', $res, false, $starti);
		$content = strMid('<p class="thread_abstract">', '</p>', $res, false, $starti);

		Db::getInstance()->exec("insert into tieba_content(pid,author_name,content,portrait,word)values($i,:name,:content,'$portrait',:word)", [':word' => $word, ':content' => $content, ':name' => $username]);

	} catch (Exception $e) {

		file_put_contents('pid.txt', '【' . date('Y-m-d H:i:s') . '】[' . $i . '] ' . $e->getMessage() . PHP_EOL, FILE_APPEND | LOCK_EX);

	}
	$index++;
	$v = intval((time() - $start_time) / 30);
	if ($v >= 1 && !in_array($v, $js)) {
		echo round($index / $count, 3) . PHP_EOL;
		array_push($js, $v);
	}

}
echo "ok" . PHP_EOL;

/*$data = ['uid' => 2];

$td = '';
$sd = '';
ksort($data);
foreach ($data as $key => $value) {
$td .= $key . '=' . $value;
$sd .= $key . '=' . $value . '&';
}

$data = $sd . 'sign=' . md5($td . 'tiebaclient!!!');
$http = new Http();
$res = json_decode($http->request(TiebaConst::APP_URL . '/c/u/user/profile', $data));

$portrait = substr($res->user->portrait, 0, 36);

$sql = "insert into tieba_user(uid,name,name_show,portriat,tb_age,like_tieba,gz,fans)values({$res->user->id},'{$res->user->name}','{$res->user->name_show}','{$portrait}',{$res->user->tb_age},{$res->user->like_forum_num},{$res->user->concern_num},{$res->user->fans_num})";
//var_dump($sql);exit();
$conf = ['DSN' => '', 'username' => '', 'password' => ''];

Db::getInstance($conf)->exec($sql);*/

?>