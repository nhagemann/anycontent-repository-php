<?php

namespace AnyContent\Service\V1Controller;

use AnyContent\Service\AbstractTest;
use Silex\Application;

class CMDLControllerTest extends AbstractTest
{

    public function testCMDLFetch()
    {
        $json = $this->getJsonResponse('/1/test/content/content1/cmdl');

        $cmdl = file_get_contents(APPLICATION_PATH . '/tmp/test/repository/cmdl/content1.cmdl');

        $this->assertEquals($cmdl, $json['cmdl']);
    }

    public function testCreateContentType()
    {
        $json = $this->getJsonResponse('/1/test/info');

        $this->assertArrayHasKey('content', $json);
        $this->assertCount(5, $json['content']);

        $cmdl = 'name';
        $json = $this->postJsonResponse('/1/test/content/content6/cmdl', 200, ['cmdl' => $cmdl]);
        $this->assertEquals(true, $json);

        $json = $this->getJsonResponse('/1/test/info');

        $this->assertArrayHasKey('content', $json);
        $this->assertCount(6, $json['content']);

        $json = $this->getJsonResponse('/1/test/content/content6/cmdl');
        $this->assertEquals($cmdl, $json['cmdl']);
    }

    public function testUpdateContentType()
    {
        $json = $this->getJsonResponse('/1/test/info');

        $this->assertArrayHasKey('content', $json);
        $this->assertCount(5, $json['content']);

        $cmdl = 'name';
        $json = $this->postJsonResponse('/1/test/content/content1/cmdl', 200, ['cmdl' => $cmdl]);
        $this->assertEquals(true, $json);

        $json = $this->getJsonResponse('/1/test/info');

        $this->assertArrayHasKey('content', $json);
        $this->assertCount(5, $json['content']);

        $json = $this->getJsonResponse('/1/test/content/content1/cmdl');
        $this->assertEquals($cmdl, $json['cmdl']);
    }

    public function testDeleteContentType()
    {
        $json = $this->getJsonResponse('/1/test/info');

        $this->assertArrayHasKey('content', $json);
        $this->assertCount(5, $json['content']);

        $json = $this->deleteJsonResponse('/1/test/content/content1', 200);
        $this->assertEquals(true, $json);

        $json = $this->getJsonResponse('/1/test/info');

        $this->assertArrayHasKey('content', $json);
        $this->assertCount(4, $json['content']);
    }

    public function testConfigType()
    {
        $json = $this->getJsonResponse('/1/test/config/config1/cmdl');

        $cmdl = file_get_contents(APPLICATION_PATH . '/tmp/test/repository/cmdl/config/config1.cmdl');

        $this->assertEquals($cmdl, $json['cmdl']);
    }

    public function testUpdateConfigType()
    {
        $json = $this->getJsonResponse('/1/test/info');

        $this->assertArrayHasKey('config', $json);
        $this->assertCount(4, $json['config']);

        $cmdl = 'name = textfield';
        $json = $this->postJsonResponse('/1/test/config/config1/cmdl', 200, ['cmdl' => $cmdl]);
        $this->assertEquals(true, $json);

        $json = $this->getJsonResponse('/1/test/info');

        $this->assertArrayHasKey('config', $json);
        $this->assertCount(4, $json['config']);

        $json = $this->getJsonResponse('/1/test/config/config1/cmdl');
        $this->assertEquals($cmdl, $json['cmdl']);
    }

    public function testDeleteConfigType()
    {
        $json = $this->getJsonResponse('/1/test/info');

        $this->assertArrayHasKey('config', $json);
        $this->assertCount(4, $json['config']);

        $json = $this->deleteJsonResponse('/1/test/config/config1', 200);
        $this->assertEquals(true, $json);

        $json = $this->getJsonResponse('/1/test/info');

        $this->assertArrayHasKey('config', $json);
        $this->assertCount(3, $json['config']);
    }
}