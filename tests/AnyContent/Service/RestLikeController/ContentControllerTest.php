<?php

namespace AnyContent\Service\RestLikeController;

use AnyContent\Client\DataDimensions;
use AnyContent\Client\Record;
use AnyContent\Service\AbstractTest;
use AnyContent\Service\Service;
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
        $tests[] = $dataDimensions;

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
                $record = new Record($repository->getCurrentContentTypeDefinition(), 'record'.$i);
                $repository->saveRecord($record);
            }

            $json = $this->getJsonResponse(
                '/1/test/content/content1/records',
                200,
                ['language' => $test->getLanguage()]
            );

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


    public function testGetRecordsDifferentViewsStep1()
    {
        $repository = $this->repository;
        $repository->selectContentType('content1');

        $this->setExpectedException('CMDL\CMDLParserException');
        $record = new Record($repository->getCurrentContentTypeDefinition(), 'test', 'default');
        $record->setProperty('a', 'avalue');
        $record->setProperty('d', 'dvalue');

    }

    public function testGetRecordsDifferentViewsStep2()
    {
        $repository = $this->repository;
        $repository->selectContentType('content1');
        $repository->selectView('import');

        $record = new Record($repository->getCurrentContentTypeDefinition(), 'test', 'import');
        $record->setProperty('a', 'avalue');
        $record->setProperty('d', 'dvalue');
        $repository->saveRecord($record);

        $json = $this->getJsonResponse('/1/test/content/content1/records/default/default');

        $this->assertArrayHasKey('records', $json);
        $this->assertCount(1, $json['records']);

        $record = array_shift($json['records']);

        $this->assertEquals('avalue', $record['properties']['a']);
        $this->assertArrayNotHasKey('d', $record['properties']);

        $json = $this->getJsonResponse('/1/test/content/content1/records/default/import');

        $this->assertArrayHasKey('records', $json);
        $this->assertCount(1, $json['records']);

        $record = array_shift($json['records']);

        $this->assertEquals('avalue', $record['properties']['a']);
        $this->assertArrayHasKey('d', $record['properties']);
        $this->assertEquals('dvalue', $record['properties']['d']);

    }

    public function testGetOrderedRecords()
    {
        $repository = $this->repository;
        $repository->selectContentType('content1');
        $record = new Record($repository->getCurrentContentTypeDefinition(), 'test');
        $record->setProperty('a', 'B');
        $repository->saveRecord($record);
        $record = new Record($repository->getCurrentContentTypeDefinition(), 'test');
        $record->setProperty('a', 'D');
        $repository->saveRecord($record);
        $record = new Record($repository->getCurrentContentTypeDefinition(), 'test');
        $record->setProperty('a', 'A');
        $repository->saveRecord($record);
        $record = new Record($repository->getCurrentContentTypeDefinition(), 'test');
        $record->setProperty('a', 'C');
        $repository->saveRecord($record);

        // Default ID order
        $json = $this->getJsonResponse('/1/test/content/content1/records');
        $this->assertArrayHasKey('records', $json);

        $record = array_shift($json['records']);
        $this->assertEquals('B', $record['properties']['a']);
        $record = array_shift($json['records']);
        $this->assertEquals('D', $record['properties']['a']);
        $record = array_shift($json['records']);
        $this->assertEquals('A', $record['properties']['a']);
        $record = array_shift($json['records']);
        $this->assertEquals('C', $record['properties']['a']);

        // Reverse ID order
        $json = $this->getJsonResponse('/1/test/content/content1/records?order=.id-');
        $this->assertArrayHasKey('records', $json);

        $record = array_shift($json['records']);
        $this->assertEquals('C', $record['properties']['a']);
        $record = array_shift($json['records']);
        $this->assertEquals('A', $record['properties']['a']);
        $record = array_shift($json['records']);
        $this->assertEquals('D', $record['properties']['a']);
        $record = array_shift($json['records']);
        $this->assertEquals('B', $record['properties']['a']);

        // order by property a
        $json = $this->getJsonResponse('/1/test/content/content1/records?order=a');
        $this->assertArrayHasKey('records', $json);

        $record = array_shift($json['records']);
        $this->assertEquals('A', $record['properties']['a']);
        $record = array_shift($json['records']);
        $this->assertEquals('B', $record['properties']['a']);
        $record = array_shift($json['records']);
        $this->assertEquals('C', $record['properties']['a']);
        $record = array_shift($json['records']);
        $this->assertEquals('D', $record['properties']['a']);

        // reverse order by property a
        $json = $this->getJsonResponse('/1/test/content/content1/records?order=a-');
        $this->assertArrayHasKey('records', $json);

        $record = array_shift($json['records']);
        $this->assertEquals('D', $record['properties']['a']);
        $record = array_shift($json['records']);
        $this->assertEquals('C', $record['properties']['a']);
        $record = array_shift($json['records']);
        $this->assertEquals('B', $record['properties']['a']);
        $record = array_shift($json['records']);
        $this->assertEquals('A', $record['properties']['a']);

        // order by property a - old query style
        $json = $this->getJsonResponse('/1/test/content/content1/records?order=property&properties=a');
        $this->assertArrayHasKey('records', $json);

        $record = array_shift($json['records']);
        $this->assertEquals('A', $record['properties']['a']);
        $record = array_shift($json['records']);
        $this->assertEquals('B', $record['properties']['a']);
        $record = array_shift($json['records']);
        $this->assertEquals('C', $record['properties']['a']);
        $record = array_shift($json['records']);
        $this->assertEquals('D', $record['properties']['a']);


    }

    public function testGetOrderedByMultiplePropertiesRecords()
    {
        $repository = $this->repository;
        $repository->selectContentType('content1');
        $record = new Record($repository->getCurrentContentTypeDefinition(), 'test');
        $record->setProperty('a', 'A');
        $record->setProperty('b', 'b');
        $record->setProperty('c', '1');
        $repository->saveRecord($record);
        $record = new Record($repository->getCurrentContentTypeDefinition(), 'test');
        $record->setProperty('a', 'B');
        $record->setProperty('b', 'a');
        $record->setProperty('c', '1');
        $repository->saveRecord($record);
        $record = new Record($repository->getCurrentContentTypeDefinition(), 'test');
        $record->setProperty('a', 'C');
        $record->setProperty('b', 'b');
        $record->setProperty('c', '2');
        $repository->saveRecord($record);
        $record = new Record($repository->getCurrentContentTypeDefinition(), 'test');
        $record->setProperty('a', 'D');
        $record->setProperty('b', 'a');
        $record->setProperty('c', '2');
        $repository->saveRecord($record);

        // order by properties - old query style
        $json = $this->getJsonResponse('/1/test/content/content1/records?order=b,c');
        $this->assertArrayHasKey('records', $json);

        $record = array_shift($json['records']);
        $this->assertEquals('B', $record['properties']['a']);
        $record = array_shift($json['records']);
        $this->assertEquals('D', $record['properties']['a']);
        $record = array_shift($json['records']);
        $this->assertEquals('A', $record['properties']['a']);
        $record = array_shift($json['records']);
        $this->assertEquals('C', $record['properties']['a']);

        // order by properties - old query style
        $json = $this->getJsonResponse('/1/test/content/content1/records?order=property&properties=b,c');
        $this->assertArrayHasKey('records', $json);

        $record = array_shift($json['records']);
        $this->assertEquals('B', $record['properties']['a']);
        $record = array_shift($json['records']);
        $this->assertEquals('D', $record['properties']['a']);
        $record = array_shift($json['records']);
        $this->assertEquals('A', $record['properties']['a']);
        $record = array_shift($json['records']);
        $this->assertEquals('C', $record['properties']['a']);
    }

    public function testGetPaginatedRecords()
    {
        $repository = $this->repository;
        $repository->selectContentType('content1');
        for ($i = 1; $i <= 12; $i++) {
            $record = new Record($repository->getCurrentContentTypeDefinition(), 'test');
            $repository->saveRecord($record);
        }
        $json = $this->getJsonResponse('/1/test/content/content1/records');
        $this->assertCount(12, $json['records']);

        $json = $this->getJsonResponse('/1/test/content/content1/records?page=1&count=10');
        $this->assertCount(10, $json['records']);

        $json = $this->getJsonResponse('/1/test/content/content1/records?page=2&count=10');
        $this->assertCount(2, $json['records']);

        $json = $this->getJsonResponse('/1/test/content/content1/records?page=3&count=10');
        $this->assertCount(0, $json['records']);

        $json = $this->getJsonResponse('/1/test/content/content1/records?page=3&count=4');
        $this->assertCount(4, $json['records']);

        $json = $this->getJsonResponse('/1/test/content/content1/records?page=4&count=4');
        $this->assertCount(0, $json['records']);
    }


    public function testGetFilteredRecords()
    {
        $repository = $this->repository;
        $repository->selectContentType('content1');
        for ($i = 1; $i <= 3; $i++) {
            for ($j = 1; $j <= 3; $j++) {
                $record = new Record($repository->getCurrentContentTypeDefinition(), 'test');
                $record->setProperty('a', $i);
                $record->setProperty('b', $j);
                $repository->saveRecord($record);
            }
        }


        $json = $this->getJsonResponse('/1/test/content/content1/records');
        $this->assertCount(9, $json['records']);

        $filter = 'a=1';
        $json = $this->getJsonResponse('/1/test/content/content1/records?filter='.urlencode($filter));
        $this->assertCount(3, $json['records']);

        $filter = 'b=1';
        $json = $this->getJsonResponse('/1/test/content/content1/records?filter='.urlencode($filter));
        $this->assertCount(3, $json['records']);

        $filter = 'b > 2';
        $json = $this->getJsonResponse('/1/test/content/content1/records?filter='.urlencode($filter));
        $this->assertCount(3, $json['records']);

        $filter = 'b >= 2';
        $json = $this->getJsonResponse('/1/test/content/content1/records?filter='.urlencode($filter));
        $this->assertCount(6, $json['records']);

        echo 'TODO';
        //@todo, more complex filtering
//        $filter = '(a=1 AND b=1)';
//        $json = $this->getJsonResponse('/1/test/content/content1/records?filter='.urlencode($filter));
//        $this->assertCount(2, $json['records']);
    }


    public function testGetSortedRecords()
    {
        echo 'TODO';
    }


    public function testAddRecord()
    {
        $repository = $this->repository;
        $repository->selectContentType('content1');

        $record = new Record($repository->getCurrentContentTypeDefinition(), 'test');
        $json = $this->postJsonResponse('/1/test/content/content1/records', 200, ['record' => json_encode($record)]);
        $this->assertEquals(1, $json);
        $json = $this->getJsonResponse('/1/test/content/content1/records');
        $this->assertCount(1, $json['records']);
    }

    public function testAddRecords1()
    {
        $repository = $this->repository;
        $repository->selectContentType('content1');

        $record = new Record($repository->getCurrentContentTypeDefinition(), 'test');
        $json = $this->postJsonResponse('/1/test/content/content1/records', 200, ['records' => json_encode([$record])]);
        $this->assertEquals([1], $json);
        $json = $this->getJsonResponse('/1/test/content/content1/records');
        $this->assertCount(1, $json['records']);
    }

    public function testAddRecords2()
    {
        $repository = $this->repository;
        $repository->selectContentType('content1');

        $records = [];
        $records[] = new Record($repository->getCurrentContentTypeDefinition(), 'test');
        $records[] = new Record($repository->getCurrentContentTypeDefinition(), 'test');
        $records[] = new Record($repository->getCurrentContentTypeDefinition(), 'test');
        $json = $this->postJsonResponse('/1/test/content/content1/records', 200, ['records' => json_encode($records)]);
        $this->assertEquals([1, 2, 3], $json);
        $json = $this->getJsonResponse('/1/test/content/content1/records');
        $this->assertCount(3, $json['records']);
    }

    public function testAddRecordNameAnnotation()
    {
        $repository = $this->repository;
        $repository->selectContentType('content2');

        $record = new Record($repository->getCurrentContentTypeDefinition(), 'test');
        $record->setProperty('firstname', 'Nils');
        $record->setProperty('lastname', 'Hagemann');

        $json = $this->postJsonResponse('/1/test/content/content2/records', 200, ['record' => json_encode($record)]);
        $this->assertEquals(1, $json);
        $json = $this->getJsonResponse('/1/test/content/content2/records');
        $this->assertCount(1, $json['records']);

        $record = array_shift($json['records']);
        $this->assertEquals('Hagemann, Nils', $record['properties']['name']);
    }

    public function testAddRecordWrongProperties()
    {
        $repository = $this->repository;
        $repository->selectContentType('content2');

        $record = new Record($repository->getCurrentContentTypeDefinition(), 'test');
        $record->setProperties(['firstname' => 'Nils', 'lastname' => 'Hagemann', 'title' => 'Dr.']);
        $json = $this->postJsonResponse('/1/test/content/content2/records', 400, ['record' => json_encode($record)]);
        $this->assertEquals(Service::ERROR_400_UNKNOWN_PROPERTIES, $json['error']['code']);

    }

    public function testDeleteRecord()
    {
        $repository = $this->repository;
        $repository->selectContentType('content1');

        $record = new Record($repository->getCurrentContentTypeDefinition(), 'test');
        $this->postJsonResponse('/1/test/content/content1/records', 200, ['records' => json_encode([$record])]);
        $record = new Record($repository->getCurrentContentTypeDefinition(), 'test');
        $this->postJsonResponse('/1/test/content/content1/records', 200, ['records' => json_encode([$record])]);
        $record = new Record($repository->getCurrentContentTypeDefinition(), 'test');
        $this->postJsonResponse('/1/test/content/content1/records', 200, ['records' => json_encode([$record])]);

        $json = $this->getJsonResponse('/1/test/content/content1/records');
        $this->assertCount(3, $json['records']);

        $json = $this->deleteJsonResponse('/1/test/content/content1/record/2', 200);
        $this->assertTrue($json);

        $json = $this->getJsonResponse('/1/test/content/content1/records');
        $this->assertCount(2, $json['records']);
    }


    public function testDeleteRecords()
    {
        $repository = $this->repository;
        $repository->selectContentType('content1');

        $record = new Record($repository->getCurrentContentTypeDefinition(), 'test');
        $this->postJsonResponse('/1/test/content/content1/records', 200, ['records' => json_encode([$record])]);
        $record = new Record($repository->getCurrentContentTypeDefinition(), 'test');
        $this->postJsonResponse('/1/test/content/content1/records', 200, ['records' => json_encode([$record])]);
        $record = new Record($repository->getCurrentContentTypeDefinition(), 'test');
        $this->postJsonResponse('/1/test/content/content1/records', 200, ['records' => json_encode([$record])]);

        $json = $this->getJsonResponse('/1/test/content/content1/records');
        $this->assertCount(3, $json['records']);

        $json = $this->deleteJsonResponse('/1/test/content/content1/records', 200);
        $this->assertTrue($json);

        $json = $this->getJsonResponse('/1/test/content/content1/records');
        $this->assertCount(0, $json['records']);
    }
}