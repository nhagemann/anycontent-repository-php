<?php

use AnyContent\Service\Service;
use Silex\Application;
use Silex\Provider\HttpCacheServiceProvider;
use Symfony\Component\Yaml\Yaml;

if (!defined('APPLICATION_PATH')) {
    define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/..'));
}

require(APPLICATION_PATH . '/vendor/autoload.php');

\KVMLogger\KVMLoggerFactory::createWithKLogger('../');

$app = new Application();

$config = Yaml::parse(file_get_contents(APPLICATION_PATH . '/config/config.yml'));
$app['acrs'] = new Service($app,$config['repositories']);

$app['debug'] = true;

//$app->register(new HttpCacheServiceProvider(), array(
//    'http_cache.cache_dir' => APPLICATION_PATH .'/var/cache',
//));
//$app['http_cache']->run();

$app->run();
