<?php

use AnyContent\Service\Service;
use Silex\Application;
use Silex\Provider\HttpCacheServiceProvider;

if (!defined('APPLICATION_PATH')) {
    define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/..'));
}

require(APPLICATION_PATH . '/vendor/autoload.php');

\KVMLogger\KVMLoggerFactory::createWithKLogger('../');

$app = new Application();
$app['acrs'] = new Service($app);

$app['debug'] = true;

//$app->register(new HttpCacheServiceProvider(), array(
//    'http_cache.cache_dir' => APPLICATION_PATH .'/var/cache',
//));
//$app['http_cache']->run();

$app->run();
