<?php

namespace AnyContent\Service\RestLikeController;

use AnyContent\Client\Record;
use AnyContent\Service\AbstractTest;
use Silex\Application;

class InfoControllerTest extends AbstractTest
{

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


    public function testRecordCountDifferentWorkspaces()
    {
        $repository = $this->repository;

        $json = $this->getJsonResponse('/1/test/info');
        $this->assertEquals(0, $json['content']['content1']['count']);

        $repository->selectContentType('content1');

        for ($i = 1; $i <= 5; $i++) {
            $record = new Record($repository->getCurrentContentTypeDefinition(), 'record' . $i);
            $repository->saveRecord($record);
        }

        $json = $this->getJsonResponse('/1/test/info');
        $this->assertEquals(5, $json['content']['content1']['count']);

        $json = $this->getJsonResponse('/1/test/info/draft');
        $this->assertEquals(0, $json['content']['content1']['count']);

        $repository->selectWorkspace('draft');

        for ($i = 1; $i <= 4; $i++) {
            $record = new Record($repository->getCurrentContentTypeDefinition(), 'record' . $i);
            $repository->saveRecord($record);
        }

        $json = $this->getJsonResponse('/1/test/info/draft');
        $this->assertEquals(4, $json['content']['content1']['count']);

    }


    public function testRecordCountDifferentLanguages()
    {
        $repository = $this->repository;

        $json = $this->getJsonResponse('/1/test/info', 200, ['language' => 'de']);
        $this->assertEquals(0, $json['content']['content1']['count']);

        $repository->selectContentType('content1');
        $repository->selectLanguage('de');

        for ($i = 1; $i <= 5; $i++) {
            $record = new Record($repository->getCurrentContentTypeDefinition(), 'record' . $i);
            $repository->saveRecord($record);
        }

        $json = $this->getJsonResponse('/1/test/info', 200, ['language' => 'de']);
        $this->assertEquals(5, $json['content']['content1']['count']);

    }

}