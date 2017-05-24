<?php

namespace AnyContent\Service\RestLikeController;

use AnyContent\Client\AbstractRecord;
use AnyContent\Client\Repository;
use AnyContent\Service\Exception\BadRequestException;
use AnyContent\Service\Exception\NotFoundException;
use AnyContent\Service\Exception\NotModifiedException;
use AnyContent\Service\Service;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class AbstractController
{

    protected static function getRepository(Application $app, Request $request, $selectContentType = true, $checkEtag = true)
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

            if ($checkEtag) {
                $etag = '"' . md5($repository->getLastModifiedDate() . '#' . $request->getUri()) . '"';
                if (self::checkEtag($request, $etag)) {
                    $e = new NotModifiedException();
                    $e->setEtag($etag);
                    throw $e;
                }
            }

            return $repository;
        }

        throw new BadRequestException(__CLASS__ . '_' . __METHOD__, Service::ERROR_400_BAD_REQUEST);
    }

    protected static function checkEtag(Request $request, $etag)
    {
        $etags = $request->getEtags();

        if (in_array($etag, $etags)) {
            return true;
        }

        return false;
    }

    protected static function checkRecord(AbstractRecord $record, $viewName)
    {
        $definition = $record->getDataTypeDefinition();
        $properties = $record->getProperties();

        // remove protected properties
        foreach ($definition->getProtectedProperties($viewName) as $property) {
            unset ($properties[$property]);
        }

        $possibleProperties = $definition->getProperties($viewName);

        $notallowed = array_diff(array_keys($properties), $possibleProperties);

        if (count($notallowed) != 0) {
            throw new BadRequestException(
                'Trying to store undefined properties: ' . join(',', $notallowed) . '.',
                Service::ERROR_400_UNKNOWN_PROPERTIES
            );
        }

        $record->setProperties($properties);
    }

    protected static function getCachedJSONResponse(Application $app, $data, Request $request, $lastModified)
    {

        $etag = md5($lastModified . '#' . $request->getUri());

        $response = $app->json($data);
        $response->setPublic();
        $response->setEtag($etag);

        return $response;
    }

}