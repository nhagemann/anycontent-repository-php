<?php

require('../vendor/autoload.php');

if (!defined('APPLICATION_PATH')) {
    define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/..'));
}

\KVMLogger\KVMLoggerFactory::createWithKLogger('../');

$service = new AnyContent\Service\Service();

$service['debug'] = true;

$service->enableHTTPCache(APPLICATION_PATH. '/var/cache');

$service->run();
