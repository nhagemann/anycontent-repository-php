<?php

namespace AnyContent\Service\Controller;

use AnyContent\Client\Repository;
use AnyContent\Service\Service;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


abstract class AbstractController
{

    protected static function createResponse(Request $request, Repository $repository, $contentTypeName = null, $configTypeName = null)
    {
        $response = new JsonResponse();
        $response->setPublic();

        $etag = $request->getPathInfo() . '#' . $request->getQueryString() . '#' . $repository->getLastModifiedDate($contentTypeName, $contentTypeName);

        $response->setEtag(md5($etag));

        return $response;
    }


    protected static function unknownRepository($repositoryName)
    {
        die ($repositoryName);
    }
//  protected static function getRepository(Service $app, $repositoryName)
//  {
//
//
//
//  }
}