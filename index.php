<?php

define('EXITFORBID', 'api');

define('DEBUG', true);

define('ROOT_PATH', dirname(__FILE__));

require './core/core.php';

Core::init();
//var_dump($_SERVER);exit();
Core::run();

?>