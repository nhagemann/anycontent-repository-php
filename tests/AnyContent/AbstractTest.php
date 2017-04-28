<?php

namespace AnyContent\Service;

use Silex\Application;

use Silex\WebTestCase;

abstract class AbstractTest extends WebTestCase
{

    public function createApplication()
    {

        $app         = new Application();
        $app['acrs'] = new Service($app);

        $app['debug'] = true;
        unset($app['exception_handler']);

        return $app;
    }


    protected function getJsonResponse($url, $code = 200)
    {
        $client = $this->createClient();
        $client->request('GET', $url);

        $response = $client->getResponse()->getContent();
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertEquals($code, $client->getResponse()->getStatusCode());

        return json_decode($response, true);
    }
}