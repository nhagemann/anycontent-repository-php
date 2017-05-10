<?php

namespace AnyContent\Service\V1Controller;

use AnyContent\Client\Record;
use AnyContent\Service\AbstractTest;
use Silex\Application;

class FilesControllerTest extends AbstractTest
{

    public function testListFiles()
    {
        $json = $this->getJsonResponse('/1/test/files');

        var_dump($json);

    }




}