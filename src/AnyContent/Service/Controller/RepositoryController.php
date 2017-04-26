<?php

namespace AnyContent\Service\Controller;

use AnyContent\Client\Repository;
use AnyContent\Service\Service;
use KVMLogger\KVMLogger;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RepositoryController extends AbstractController
{

    public static function init(Service $app)
    {

        $app->get('/1/{repositoryName}/info', __CLASS__ . '::index');

    }


    public static function index(Service $app, Request $request, $repositoryName)
    {

        /** @var Repository $repository */
        $repository = $app->getClient()->getRepository($repositoryName);

        if ($repository)
        {
            $response = self::createResponse($request, $repository, null, null);
            if ($response->isNotModified($request))
            {
                KVMLogger::instance('anycontent-service')->debug('cached');

                return $response;
            }
            KVMLogger::instance('anycontent-service')->debug('new');

            $result = [ ];

            $contentTypes = $repository->getContentTypeDefinitions();
            foreach ($contentTypes as $contentTypeDefinition)
            {
                $result['content'][$contentTypeDefinition->getName()] = [ 'title' => $contentTypeDefinition->getTitle() ];
            }

            $response->setContent(json_encode($result, JSON_PRETTY_PRINT));

            return $response;
        }

        return self::unknownRepository($repositoryName);
    }
}