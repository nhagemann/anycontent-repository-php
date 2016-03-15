<?php
if (!defined('APPLICATION_PATH'))
{
    define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/..'));
}

require_once __DIR__ . '/../vendor/autoload.php';

$service = new AnyContent\Repository\Service();

$service['debug']=true;

$client = new \AnyContent\Client\Client();

$configuration = new \AnyContent\Connection\Configuration\RestLikeConfiguration();
$configuration->setUri('http://acrs.hahnair.dev/1/test');
$connection = $configuration->createReadWriteConnection();
$configuration->addContentTypes();

$repository = new \AnyContent\Client\Repository('test', $connection);


$client->addRepository($repository);

$service->setClient($client);


$service->register(new Silex\Provider\HttpCacheServiceProvider(), array(
    'http_cache.cache_dir' => APPLICATION_PATH.'/http_cache/',
));
\Symfony\Component\HttpFoundation\Request::setTrustedProxies(array('127.0.0.1'));
$service['http_cache']->run();

//$service->run();