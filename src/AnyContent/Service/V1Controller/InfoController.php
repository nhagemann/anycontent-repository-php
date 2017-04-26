<?php

namespace AnyContent\Service\V1Controller;

use AnyContent\Service\Service;
use Silex\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class InfoController extends AbstractController
{

    public static function init(Service $app)
    {

        $app->get('/1/{repositoryName}/info', __CLASS__ . '::index');
        $app->get('/1/{repositoryName}/info/{workspace}', __CLASS__ . '::index');

        $app->get('/1/{repositoryName}', __CLASS__ . '::redirect');
    }


    public static function index(Service $app, Request $request, $repositoryName)
    {
        $repository = self::getRepository($app, $request, $repositoryName);

        return self::getCachedJSONResponse($app, $repository, $repository);
    }

    public static function redirect(Service $app, Request $request, $repositoryName)
    {
        return new RedirectResponse('/1/'.$repositoryName.'/info',301);

    }
}