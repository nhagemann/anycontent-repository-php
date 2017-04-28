<?php

namespace AnyContent\Service\V1Controller;

use AnyContent\Service\Service;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class InfoController extends AbstractController
{

    public static function init(Application $app)
    {

        $app->get('/1/{repositoryName}/info', __CLASS__ . '::index');
        $app->get('/1/{repositoryName}/info/{workspace}', __CLASS__ . '::index');

        $app->get('/1/{repositoryName}', __CLASS__ . '::redirect');

        $app->get('/', __CLASS__ . '::welcome');
        $app->get('/1', __CLASS__ . '::welcome');
        $app->get('/1/', __CLASS__ . '::welcome');
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


    public static function redirect(Application $app, Request $request, $repositoryName)
    {
        return new RedirectResponse('/1/' . $repositoryName . '/info', 301);

    }


    public static function welcome(Application $app, Request $request)
    {
        if ($request->getRequestUri() != '/1') {
            return new RedirectResponse('/1', 301);
        }

        return new JsonResponse('Welcome to AnyContent Repository Server. Please specify desired repository.');

    }
}