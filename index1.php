<?php

//require './init.php';
//phpinfo();
//4E00-9FA5
//19968
//40869
//$cc = unpack('H*', 's');
//var_dump(base_convert(‘9FA5’, 16, 10));

/*$ccc = '\\u' . base_convert(mt_rand(19968, 40869), 10, 16);

//echo (json_decode('{"str":"' . $ccc . '"}', true)['str']);

function UnicodeEncode($str) {
//split word
preg_match_all('/./u', $str, $matches);

$unicodeStr = "";
foreach ($matches[0] as $m) {
//拼接
$unicodeStr .= "&#" . base_convert(bin2hex(iconv('UTF-8', "UCS-4", $m)), 16, 10);
}
return $unicodeStr;
}

$str = "新浪微博";
echo UnicodeEncode($str);*/

//var_dump(Db::getInstance());
//sendMail('wbreport故障', '<h1>cookie失效</h1>', 'xxue@live.cn');

/*$server = new Swoole\WebSocket\Server("127.0.0.1", 9501);

$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
echo PHP_EOL;
$fd = $request->fd; //获取客户端请求的文件描述符
echo "[open] client {$fd} handshake success" . PHP_EOL;
echo "[server] " . json_encode($server) . PHP_EOL;
echo "[request] " . json_encode($request) . PHP_EOL;
});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
echo PHP_EOL;
$fd = $frame->fd; //获取客户端请求的文件描述符
$data = $frame->data; //获取客户端发送的消息
echo "[message] client {$fd} : {$data}" . PHP_EOL;
echo "[server] " . json_encode($server) . PHP_EOL;
echo "[frame] " . json_encode($frame) . PHP_EOL;

$message = "success";
$server->push($fd, $message);
});

$server->on('close', function ($ser, $fd) {
echo PHP_EOL;
echo "[close] client {$fd}" . PHP_EOL;
echo "[server] " . json_encode($server) . PHP_EOL;
echo "[fd] " . json_encode($fd) . PHP_EOL;
});

$server->start();*/

//$fds = [];

/*$server = new Swoole\WebSocket\Server("127.0.0.1", 9503);

$server->on('open', function (Swoole\WebSocket\Server $server, $request) {

$server->push($request->fd, 'welcome:' . $request->fd . '-' . $request->server->remote_addr);

});
$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
//echo PHP_EOL;
$fd = $frame->fd; //获取客户端请求的文件描述符
$data = $frame->data; //获取客户端发送的消息
echo $fd . ':' . $data . PHP_EOL;
$server->push($fd, time());
});

$server->start();*/

/*$w = new Weibo();

echo $w->sign('10080809efa465aa718c634894e8a868d8fccc', 'SUB=_2A25wr7nuDeRhGeNH61UU9C_FwjuIHXVQU8emrDV6PUJbkdAKLWP5kW1NSvUrHXq7X1mwz6dPG85susnqKoZWcBF1');
//echo json_encode($_SERVER);*/
//puan7450134@163.com----asd123123.
//13392195189----huawei123456.
//
//

;?>






