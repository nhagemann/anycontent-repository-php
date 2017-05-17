<?php

define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/..'));

$loader = require __DIR__ . "/../vendor/autoload.php";
$loader->add('AnyContent', __DIR__.'/AnyContent');

return $loader;



