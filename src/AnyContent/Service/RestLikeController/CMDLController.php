<?php

namespace AnyContent\Service\RestLikeController;

use AnyContent\Connection\Interfaces\AdminConnection;
use AnyContent\Service\Exception\BadRequestException;
use AnyContent\Service\Service;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CMDLController extends AbstractController
{

    public static function init(Application $app, $path)
    {

        // get cmdl for a content type
        $app->get($path.'/{repositoryName}/content/{contentTypeName}/cmdl', __CLASS__ . '::getContentTypeCMDL');
        $app->get($path.'/{repositoryName}/content/{contentTypeName}/cmdl/{locale}', __CLASS__ . '::getContentTypeCMDL');

        // update cmdl for a content type / create content type
        $app->post($path.'/{repositoryName}/content/{contentTypeName}/cmdl', __CLASS__ . '::postContentTypeCMDL');
        $app->post($path.'/{repositoryName}/content/{contentTypeName}/cmdl/{locale}', __CLASS__ . '::postContentTypeCMDL');

        // delete content type
        $app->delete($path.'/{repositoryName}/content/{contentTypeName}', __CLASS__ . '::deleteContentTypeCMDL');

        // get cmdl for a config type
        $app->get($path.'/{repositoryName}/config/{configTypeName}/cmdl', __CLASS__ . '::getConfigTypeCMDL');
        $app->get($path.'/{repositoryName}/config/{configTypeName}/cmdl/{locale}', __CLASS__ . '::getConfigTypeCMDL');

        // update cmdl for a config type / create config type
        $app->post($path.'/{repositoryName}/config/{configTypeName}/cmdl', __CLASS__ . '::postConfigTypeCMDL');
        $app->post($path.'/{repositoryName}/config/{configTypeName}/cmdl/{locale}', __CLASS__ . '::postConfigTypeCMDL');

        // delete config type
        $app->delete($path.'/{repositoryName}/config/{configTypeName}', __CLASS__ . '::deleteConfigTypeCMDL');
    }

    public static function getContentTypeCMDL(Application $app, Request $request, $repositoryName, $contentTypeName, $locale = 'en')
    {
        $repository = self::getRepository($app, $request);

        $definition = $repository->getCurrentContentTypeDefinition();

        return self::getCachedJSONResponse($app, ['cmdl' => $definition->getCMDL()], $request, $repository);
    }

    public static function postContentTypeCMDL(Application $app, Request $request, $repositoryName, $contentTypeName, $locale = 'en')
    {
        $repository = self::getRepository($app, $request, false);

        $connection = $repository->getWriteConnection();

        if ($connection instanceof AdminConnection) {

            if ($request->request->has('cmdl')) {
                $cmdl = $request->request->get('cmdl');
                $connection->saveContentTypeCMDL($contentTypeName, $cmdl);

                return new JsonResponse(true);
            }
        }

        throw new BadRequestException(__CLASS__ . '_' . __METHOD__, Service::ERROR_400_BAD_REQUEST);
    }

    public static function deleteContentTypeCMDL(Application $app, Request $request, $repositoryName, $contentTypeName, $locale = 'en')
    {
        $repository = self::getRepository($app, $request, false);

        $connection = $repository->getWriteConnection();

        if ($connection instanceof AdminConnection) {

            $connection->deleteContentTypeCMDL($contentTypeName);

            return new JsonResponse(true);
        }

        throw new BadRequestException(__CLASS__ . '_' . __METHOD__, Service::ERROR_400_BAD_REQUEST);
    }

    public static function getConfigTypeCMDL(Application $app, Request $request, $repositoryName, $configTypeName, $locale = 'en')
    {
        $repository = self::getRepository($app, $request);

        $definition = $repository->getConfigTypeDefinition($configTypeName);

        return self::getCachedJSONResponse($app, ['cmdl' => $definition->getCMDL()], $request, $repository);
    }

    public static function postConfigTypeCMDL(Application $app, Request $request, $repositoryName, $configTypeName, $locale = 'en')
    {
        $repository = self::getRepository($app, $request, false);

        $connection = $repository->getWriteConnection();

        if ($connection instanceof AdminConnection) {

            if ($request->request->has('cmdl')) {
                $cmdl = $request->request->get('cmdl');
                $connection->saveConfigTypeCMDL($configTypeName, $cmdl);

                return new JsonResponse(true);
            }
        }

        throw new BadRequestException(__CLASS__ . '_' . __METHOD__, Service::ERROR_400_BAD_REQUEST);
    }

    public static function deleteConfigTypeCMDL(Application $app, Request $request, $repositoryName, $configTypeName, $locale = 'en')
    {
        $repository = self::getRepository($app, $request, false);

        $connection = $repository->getWriteConnection();

        if ($connection instanceof AdminConnection) {

            $connection->deleteConfigTypeCMDL($configTypeName);

            return new JsonResponse(true);
        }

        throw new BadRequestException(__CLASS__ . '_' . __METHOD__, Service::ERROR_400_BAD_REQUEST);
    }

}