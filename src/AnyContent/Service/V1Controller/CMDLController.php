<?php

namespace AnyContent\Service\V1Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;


class CMDLController extends AbstractController
{

    public static function init(Application $app)
    {

        // get cmdl for a content type
        $app->get('/1/{repositoryName}/content/{contentTypeName}/cmdl', __CLASS__.'::getContentTypeCMDL');
        $app->get('/1/{repositoryName}/content/{contentTypeName}/cmdl/{locale}', __CLASS__.'::getContentTypeCMDL');

        // update cmdl for a content type / create content type
        $app->post('/1/{repositoryName}/content/{contentTypeName}/cmdl', __CLASS__.'::postContentTypeCMDL');
        $app->post('/1/{repositoryName}/content/{contentTypeName}/cmdl/{locale}', __CLASS__.'::postContentTypeCMDL');

        // delete content type
        $app->post('/1/{repositoryName}/content/{contentTypeName}', __CLASS__.'::deleteContentTypeCMDL');

        // get cmdl for a config type
        $app->get('/1/{repositoryName}/config/{configTypeName}/cmdl', __CLASS__.'::getConfigTypeCMDL');
        $app->get('/1/{repositoryName}/config/{configTypeName}/cmdl/{locale}', __CLASS__.'::getConfigTypeCMDL');

        // update cmdl for a config type / create config type
        $app->post('/1/{repositoryName}/config/{configTypeName}/cmdl', __CLASS__.'::postConfigTypeCMDL');
        $app->post('/1/{repositoryName}/config/{configTypeName}/cmdl/{locale}', __CLASS__.'::postConfigTypeCMDL');

        // delete config type
        $app->post('/1/{repositoryName}/config/{configTypeName}', __CLASS__.'::deleteConfigTypeCMDL');

    }

    public static function getContentTypeCMDL(
        Application $app,
        Request $request,
        $repositoryName,
        $contentTypeName,
        $locale = 'en'
    ) {
        $repository = self::getRepository($app, $request);

        $definition = $repository->getCurrentContentTypeDefinition();

        return self::getCachedJSONResponse($app, ['cmdl'=>$definition->getCMDL()], $request, $repository);

    }

}