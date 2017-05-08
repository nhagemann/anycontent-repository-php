<?php

namespace AnyContent\Service;

use AnyContent\Client\Repository;
use Silex\Application;

use Silex\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractTest extends WebTestCase
{

    /** @var  Repository */
    protected $repository;


    public function createApplication()
    {
        $fs = new Filesystem();

        if ($fs->exists(APPLICATION_PATH . '/tmp/test')) {
            $fs->remove(APPLICATION_PATH . '/tmp/test');
        }

        $fs->mkdir(APPLICATION_PATH . '/tmp/test');
        $fs->mirror(APPLICATION_PATH . '/tests/resources/repository', APPLICATION_PATH . '/tmp/test/repository');

        $config         = [];
        $config['test'] = ['type' => "archive", 'folder' => APPLICATION_PATH . '/tmp/test/repository', 'files' => true];

        $app         = new Application();
        $app['acrs'] = new Service($app, $config);

        $this->repository = $app['acrs']->getRepository('test');

        $app['debug'] = true;
        unset($app['exception_handler']);

        return $app;
    }


    protected function getJsonResponse($url, $code = 200, $params = [])
    {
        $client = $this->createClient();
         $client->request('GET', $url, $params);

        $response = $client->getResponse()->getContent();
        $this->assertEquals($code, $client->getResponse()->getStatusCode(),'Wrong http Status code:');

        return json_decode($response, true);
    }

    protected function postJsonResponse($url, $code = 200, $params = [])
    {
        $client = $this->createClient();
        $client->request('POST', $url, $params);

        $response = $client->getResponse()->getContent();
        $this->assertEquals($code, $client->getResponse()->getStatusCode(),'Wrong http Status code:');

        return json_decode($response, true);
    }

    protected function deleteJsonResponse($url, $code = 200, $params = [])
    {
        $client = $this->createClient();
        $client->request('DELETE', $url, $params);

        $response = $client->getResponse()->getContent();
        $this->assertEquals($code, $client->getResponse()->getStatusCode(),'Wrong http Status code:');

        return json_decode($response, true);
    }
}