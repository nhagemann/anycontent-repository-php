<?php

namespace AnyContent\Service\RestLikeController;

use AnyContent\Service\Service;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class InfoController extends AbstractController
{

    public static function init(Application $app, $path)
    {

        // Get info about content and config types of a repository.
        // You may specify workspace and language to adjust record count and lastchange info.
        // Will work with any workspaces/language, no check if the given workspaces/language is actually used
        // within any data type.
        //
        $app->get($path . '/{repositoryName}/info', __CLASS__ . '::index');
        $app->get($path . '/{repositoryName}/info/{workspace}', __CLASS__ . '::index');

        // Shortcut
        $app->get($path . '/{repositoryName}', __CLASS__ . '::redirect')->value('path', $path);

        // Welcome Message
        $app->get($path, __CLASS__ . '::welcome')->value('path', $path);
        $app->get($path . '/', __CLASS__ . '::welcome');
    }

    /**
     * @param Service $app
     * @param Request $request
     * @param         $repositoryName
     * @param string  $workspace
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \AnyContent\Service\Exception\BadRequestException
     * @throws \AnyContent\Service\Exception\NotFoundException
     */
    public static function index(Application $app, Request $request, $repositoryName, $workspace = 'default')
    {
        $repository = self::getRepository($app, $request);

        return self::getCachedJSONResponse($app, $repository, $request, $repository);
    }

    public static function redirect(Application $app, Request $request, $repositoryName, $path)
    {

        return new RedirectResponse($path . '/' . $repositoryName . '/info', 301);
    }

    public static function welcome(Application $app, Request $request)
    {

        if (substr($request->getRequestUri(), -1) === '/') {
            return new RedirectResponse(substr($request->getRequestUri(), 0, -1), 301);
        }

        return new JsonResponse('Welcome to AnyContent Repository Server. Please specify desired repository.');
    }
}