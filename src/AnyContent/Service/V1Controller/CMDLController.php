<?php

namespace AnyContent\Service\V1Controller;

use AnyContent\Service\Service;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class CMDLController extends AbstractController
{

    public static function init(Application $app)
    {

        // get cmdl for a content type
        $app->get('/1/{repositoryName}/{contentTypeName}/cmdl', __CLASS__ . '::getContentTypeCMDL');
        $app->get('/1/{repositoryName}/{contentTypeName}/cmdl/{locale}', __CLASS__ . '::getContentTypeCMDL');

        // update cmdl for a content type / create content type
        $app->post('/1/{repositoryName}/{contentTypeName}/cmdl', __CLASS__ . '::postContentTypeCMDL');
        $app->post('/1/{repositoryName}/{contentTypeName}/cmdl/{locale}', __CLASS__ . '::postContentTypeCMDL');

        // delete content type
        $app->post('/1/{repositoryName}/{contentTypeName}', __CLASS__ . '::deleteContentTypeCMDL');

        // get cmdl for a config type
        $app->get('/1/{repositoryName}/{configTypeName}/cmdl', __CLASS__ . '::getConfigTypeCMDL');
        $app->get('/1/{repositoryName}/{configTypeName}/cmdl/{locale}', __CLASS__ . '::getConfigTypeCMDL');

        // update cmdl for a config type / create config type
        $app->post('/1/{repositoryName}/{configTypeName}/cmdl', __CLASS__ . '::postConfigTypeCMDL');
        $app->post('/1/{repositoryName}/{configTypeName}/cmdl/{locale}', __CLASS__ . '::postConfigTypeCMDL');

        // delete config type
        $app->post('/1/{repositoryName}/{configTypeName}', __CLASS__ . '::deleteConfigTypeCMDL');

    }

}