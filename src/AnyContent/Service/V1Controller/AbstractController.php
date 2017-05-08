<?php

namespace AnyContent\Service\V1Controller;

use AnyContent\Client\Repository;
use AnyContent\Service\Exception\BadRequestException;
use AnyContent\Service\Exception\NotFoundException;
use AnyContent\Service\Exception\NotModifiedException;
use AnyContent\Service\Service;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class AbstractController
{

    protected static function getRepository(Application $app, Request $request, $selectContentType = true)
    {
        $definition     = null;
        $repositoryName = $request->attributes->get('repositoryName');
        $workspace      = $request->attributes->get('workspace', 'default');
        $viewName       = $request->attributes->get('viewName', 'default');
        $language       = $request->query->get('language', 'default');

        /** @var Repository $repository */
        $repository = $app['acrs']->getRepository($repositoryName);

        // Select and check content type, if requested
        if ($selectContentType == true) {
            if ($request->attributes->has('contentTypeName')) {
                $contentTypeName = $request->attributes->get('contentTypeName');
                if (!$repository->hasContentType($contentTypeName)) {
                    throw new NotFoundException(
                        'Unknown content type ' . $contentTypeName . ' within repository ' . $repository->getName() . '.',
                        Service::ERROR_404_UNKNOWN_CONTENTTYPE
                    );
                }
                $repository->selectContentType($contentTypeName);
                $definition = $repository->getCurrentContentTypeDefinition();

                if (!$definition->hasWorkspace($workspace)) {
                    throw new NotFoundException(
                        'Unknown workspace ' . $workspace . ' for content type ' . $contentTypeName . '.',
                        Service::ERROR_404_UNKNOWN_WORKSPACE
                    );
                }
                if (!$definition->hasLanguage($language)) {
                    throw new NotFoundException(
                        'Unknown language ' . $language . ' for content type ' . $contentTypeName . '.',
                        Service::ERROR_404_UNKNOWN_LANGUAGE
                    );
                }
                if (!$definition->hasViewDefinition($viewName)) {
                    throw new NotFoundException(
                        'Unknown view ' . $viewName . ' for content type ' . $contentTypeName . '.',
                        Service::ERROR_404_UNKNOWN_VIEW
                    );
                }
            }
        }

        // Set data dimensions

        $repository->selectWorkspace($workspace);
        $repository->selectLanguage($language);
        $repository->selectView($viewName);

        // @TODO timeshift

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

        throw new BadRequestException(__CLASS__ . '_' . __METHOD__, Service::ERROR_400_BAD_REQUEST);
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