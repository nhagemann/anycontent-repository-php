<?php

namespace AnyContent\Service\V1Controller;

use AnyContent\Client\Record;
use AnyContent\Service\AbstractTest;
use AnyContent\Service\Service;
use Silex\Application;

class FilesControllerTest extends AbstractTest
{

    public function testListFilesStructure()
    {
        $json = $this->getJsonResponse('/1/test/files');

        $this->assertArrayHasKey('folders', $json);
        $this->assertArrayHasKey('files', $json);

        $json = $this->getJsonResponse('/1/test/files/notexisting');

        $this->assertFalse($json);
    }

    public function testListFilesCount()
    {
        $json = $this->getJsonResponse('/1/test/files/example');

        $this->assertArrayHasKey('folders', $json);
        $this->assertCount(2, $json['folders']);
        $this->assertContains('Movies', $json['folders']);
        $this->assertContains('Music', $json['folders']);
        $this->assertArrayHasKey('files', $json);
        $this->assertCount(3, $json['files']);
        $this->assertContains('a.txt', array_keys($json['files']));
        $this->assertContains('b.txt', array_keys($json['files']));
        $this->assertContains('len_std.jpg', array_keys($json['files']));
    }

    public function testFileInfo()
    {
        $json = $this->getJsonResponse('/1/test/files/example');

        $info = $json['files']['b.txt'];

        $this->assertEquals('example/b.txt', $info['id']);
        $this->assertEquals('b.txt', $info['name']);
        $this->assertEquals('binary', $info['type']);
        $this->assertEquals(5, $info['size']);

        $info = $json['files']['len_std.jpg'];

        $this->assertEquals('example/len_std.jpg', $info['id']);
        $this->assertEquals('len_std.jpg', $info['name']);
        $this->assertEquals('image', $info['type']);
        $this->assertEquals(20401, $info['size']);
    }

    public function testGetFile()
    {
        $content = $this->getResponse('/1/test/file/example/a.txt', 200);

        $this->assertEquals('a.txt', $content);

        $json = $this->getJsonResponse('/1/test/file/example/z.txt', 404);

        $this->assertEquals(Service::ERROR_404_FILE_NOT_FOUND, $json['error']['code']);
    }

    public function testPostFile()
    {
        $json = $this->getJsonResponse('/1/test/file/example/new.txt', 404);

        $this->assertEquals(Service::ERROR_404_FILE_NOT_FOUND, $json['error']['code']);

        // Create File

        $json = $this->postJsonResponse('/1/test/file/example/new.txt', 200, [], 'new.txt');

        $this->assertTrue($json);

        $content = $this->getResponse('/1/test/file/example/new.txt', 200);

        $this->assertEquals('new.txt', $content);

        // Again - update

        $json = $this->postJsonResponse('/1/test/file/example/new.txt', 200, [], 'new.txt');

        $this->assertTrue($json);

        $content = $this->getResponse('/1/test/file/example/new.txt', 200);

        $this->assertEquals('new.txt', $content);
    }

    public function testDeleteFile()
    {
        $json = $this->getJsonResponse('/1/test/file/example/new.txt', 404);

        $this->assertEquals(Service::ERROR_404_FILE_NOT_FOUND, $json['error']['code']);

        // Create File

        $json = $this->postJsonResponse('/1/test/file/example/new.txt', 200, [], 'new.txt');

        $this->assertTrue($json);

        $content = $this->getResponse('/1/test/file/example/new.txt', 200);

        $this->assertEquals('new.txt', $content);

        // Delete File

        $json = $this->deleteJsonResponse('/1/test/file/example/new.txt', 200);

        $this->assertTrue($json);

        $json = $this->getJsonResponse('/1/test/file/example/new.txt', 404);

        $this->assertEquals(Service::ERROR_404_FILE_NOT_FOUND, $json['error']['code']);
    }

    public function testDeleteFolder()
    {
        $json = $this->getJsonResponse('/1/test/files/example');

        $this->assertArrayHasKey('folders', $json);
        $this->assertCount(2, $json['folders']);
        $this->assertContains('Movies', $json['folders']);
        $this->assertContains('Music', $json['folders']);

        $json = $this->deleteJsonResponse('/1/test/files/example/Music');

        $this->assertTrue($json);

        $json = $this->getJsonResponse('/1/test/files/example');

        $this->assertArrayHasKey('folders', $json);
        $this->assertCount(1, $json['folders']);
        $this->assertContains('Movies', $json['folders']);
        $this->assertNotContains('Music', $json['folders']);
    }


    public function testCreateFolder()
    {
        $json = $this->getJsonResponse('/1/test/files/example');

        $this->assertArrayHasKey('folders', $json);
        $this->assertCount(2, $json['folders']);
        $this->assertContains('Movies', $json['folders']);
        $this->assertContains('Music', $json['folders']);

        $json = $this->postJsonResponse('/1/test/files/example/Games');

        $this->assertTrue($json);

        $json = $this->getJsonResponse('/1/test/files/example');

        $this->assertArrayHasKey('folders', $json);
        $this->assertCount(3, $json['folders']);
        $this->assertContains('Movies', $json['folders']);
        $this->assertContains('Music', $json['folders']);
        $this->assertContains('Games', $json['folders']);

        $json = $this->postJsonResponse('/1/test/files/example/Games');

        $this->assertTrue($json);
    }
}