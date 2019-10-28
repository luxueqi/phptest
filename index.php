<?php

define('EXITFORBID', 'api');

define('DEBUG', false);

require './core.php';

Core::init();

Apim::run();

?>