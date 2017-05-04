<?php

namespace AnyContent\Service\V1Controller;

use AnyContent\AnyContentClientException;
use AnyContent\Client\Repository;
use AnyContent\Service\Exception\BadRequestException;
use AnyContent\Service\Exception\NotFoundException;
use AnyContent\Service\Exception\NotModifiedException;
use AnyContent\Service\Service;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class AbstractController
{

    protected static function getRepository(Application $app, Request $request)
    {
        $repositoryName = $request->attributes->get('repositoryName');
        $workspace = $request->attributes->get('workspace', 'default');
        $viewName  = $request->attributes->get('viewName', 'default');
        $language  = $request->query->get('language', 'default');

        /** @var Repository $repository */
        $repository = $app['acrs']->getRepository($repositoryName);

        if ($request->attributes->has('contentTypeName'))
        {
            $contentTypeName = $request->attributes->get('contentTypeName');
            if (!$repository->hasContentType($contentTypeName))
            {
                throw new NotFoundException('Unknown content type ' . $contentTypeName.' within repository '.$repository->getName().'.',3);
            }
            $repository->selectContentType($contentTypeName);
        }

        $repository->selectWorkspace($workspace);
        $repository->selectLanguage($language);
        $repository->selectView($viewName);

        if ($repository) {

            $etag  = md5($repository->getLastModifiedDate() . '#' . $request->getUri());
            $etags = $request->getEtags();

            if (in_array($etag, $etags)) {
                $e = new NotModifiedException();
                $e->setEtag($etag);
                throw $e;
            }

            return $repository;

        }

        throw new BadRequestException(__CLASS__, __METHOD__);
    }


    protected static function getCachedJSONResponse(Application $app, $data, Request $request, Repository $repository)
    {

        $etag = md5($repository->getLastModifiedDate() . '#' . $request->getUri());

        $response = $app->json($data);
        $response->setPublic();
        $response->setEtag($etag);

        return $response;
    }

}