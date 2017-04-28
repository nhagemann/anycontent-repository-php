<?php

namespace AnyContent\Service;

use Silex\Application;

class CMDLControllerTest extends AbstractTest
{

    public function testXY()
    {
        $json = $this->getJsonResponse('/');

        $this->assertEquals('Welcome to AnyContent Repository Server. Please specify desired repository.',$json);


    }
}