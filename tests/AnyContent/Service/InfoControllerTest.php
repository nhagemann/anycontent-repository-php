<?php

namespace AnyContent\Service;

use Silex\Application;

class InfoControllerTest extends AbstractTest
{

    public function testWelcomeRoute()
    {
        $json = $this->getJsonResponse('/');

        $this->assertEquals('Welcome to AnyContent Repository Server. Please specify desired repository.', $json);

    }


    public function testRepositoryInfo()
    {
        $json = $this->getJsonResponse('/1/test');

        $this->assertArrayHasKey('content', $json);
        $this->assertCount(5, $json['content']);

        $this->assertArrayHasKey('config', $json);
        $this->assertCount(4, $json['config']);

        $this->assertArrayHasKey('files', $json);
        $this->assertArrayHasKey('admin', $json);

    }
}