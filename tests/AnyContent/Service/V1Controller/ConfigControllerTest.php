<?php

namespace AnyContent\Service\V1Controller;

use AnyContent\Client\Config;
use AnyContent\Client\DataDimensions;
use AnyContent\Client\Record;
use AnyContent\Service\AbstractTest;
use AnyContent\Service\Service;
use Silex\Application;

class ConfigControllerTest extends AbstractTest
{

    public function testSaveAndRetrieveConfig()
    {
        $repository = $this->repository;

        $json = $this->getJsonResponse('/1/test/config/config1/record');

        $this->assertArrayHasKey('record', $json);
        $this->assertArrayHasKey('properties', $json['record']);
        $this->assertCount(0, $json['record']['properties']);

        $record = new Config($repository->getConfigTypeDefinition('config1'));
        $record->setProperty('name', 'testvalue');
        $json = $this->postJsonResponse('/1/test/config/config1/record', 200, ['record' => json_encode($record)]);
        $this->assertTrue($json);

        $json = $this->getJsonResponse('/1/test/config/config1/record');

        $this->assertArrayHasKey('record', $json);
        $this->assertArrayHasKey('properties', $json['record']);
        $this->assertCount(1, $json['record']['properties']);
        $this->assertEquals('testvalue', $json['record']['properties']['name']);

        $json = $this->getJsonResponse('/1/test/config/config1/record/draft');

        $this->assertArrayHasKey('record', $json);
        $this->assertArrayHasKey('properties', $json['record']);
        $this->assertCount(0, $json['record']['properties']);
    }

}