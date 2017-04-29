<?php

namespace AnyContent\Service\V1Controller;

use AnyContent\Client\DataDimensions;
use AnyContent\Client\Record;
use AnyContent\Service\AbstractTest;
use Silex\Application;

class ContentControllerTest extends AbstractTest
{

    public function testGetRecordsDifferentDataDimensions()
    {
        $repository = $this->repository;

        $json = $this->getJsonResponse('/1/test/content/content1/records');

        $this->assertArrayHasKey('records', $json);
        $this->assertCount(0, $json['records']);

        $tests = [];

        $dataDimensions = new DataDimensions();
        $tests[]        = $dataDimensions;

        $dataDimensions = new DataDimensions();
        $dataDimensions->setWorkspace('draft');
        $tests[] = $dataDimensions;

        $dataDimensions = new DataDimensions();
        $dataDimensions->setLanguage('de');
        $tests[] = $dataDimensions;

        foreach ($tests as $test) {

            $repository->selectContentType('content1');
            $repository->setDataDimensions($test);

            for ($i = 1; $i <= 5; $i++) {
                $record = new Record($repository->getCurrentContentTypeDefinition(), 'record' . $i);
                $repository->saveRecord($record);
            }

            $json = $this->getJsonResponse('/1/test/content/content1/records', 200,
                ['language' => $test->getLanguage()]);

            $this->assertArrayHasKey('records', $json);
            $this->assertCount(5, $json['records']);

            $this->assertArrayHasKey('info', $json);
            $info = $json['info'];

            $this->assertEquals($test->getWorkspace(), $info['workspace']);
            $this->assertEquals($test->getLanguage(), $info['language']);
            $this->assertEquals(5, $info['count']);
            $this->assertEquals('content1', $info['content_type']);
        }

    }


    public function testGetRecordsDifferentViews()
    {
        echo 'TODO';
    }


    public function testGetOrderedRecords()
    {
        echo 'TODO';
    }


    public function testGetFilteredRecords()
    {
        echo 'TODO';
    }


    public function testGetSortedRecords()
    {
        echo 'TODO';
    }


    public function testAddRecords()
    {
        echo 'TODO';
    }


    public function testDeleteRecord()
    {
        echo 'TODO';
    }


    public function testDeleteRecords()
    {
        echo 'TODO';
    }
}