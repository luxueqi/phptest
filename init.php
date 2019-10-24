<?php
header('Content-type:text/html;charset=utf-8');

date_default_timezone_set('PRC');

define('DEBUG', true);

if (DEBUG) {
	ini_set('display_errors', 'On');

	error_reporting(E_ALL);

} else {
	ini_set('display_errors', 'Off');

	//error_reporting(0);

}

require './lib/function/common.php';

spl_autoload_register(function ($className) {

	require ('./lib/class/' . $className . '.class.php');

}, TRUE, TRUE);
//
?>