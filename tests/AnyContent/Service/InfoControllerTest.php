<?php

namespace AnyContent\Service;

use Silex\Application;

class InfoControllerTest extends AbstractTest
{

    public function testWelcomeMessage()
    {
        $json = $this->getJsonResponse('/');

        $this->assertEquals('Welcome to AnyContent Repository Server. Please specify desired repository.',$json);


    }
}