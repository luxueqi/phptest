<?php

define('EXITFORBID', 'api');

define('DEBUG', false);

define('ROOT_PATH', dirname(__FILE__));

require './core/core.php';

Core::init();
//var_dump($_SERVER);exit();
//captcha();

Core::run();

?>