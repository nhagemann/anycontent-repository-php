<?php

namespace AnyContent\Service\V1Controller;


use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class ContentController extends AbstractController
{

    public static function init(Application $app)
    {

        $app->get('/1/{repositoryName}/content/{contentTypeName}/records', __CLASS__ . '::index');
        $app->get('/1/{repositoryName}/content/{contentTypeName}/records/{workspace}', __CLASS__ . '::index');
        $app->get('/1/{repositoryName}/content/{contentTypeName}/records/{workspace}/{viewName}',
            __CLASS__ . '::index');


        /*
         *  // list content
        $app->get('/1/{repositoryName}/content', 'AnyContent\Repository\Modules\Core\ContentRecords\ContentController::index');

        // get record (additional query parameters: timeshift, language)
        $app->get('/1/{repositoryName}/content/{contentTypeName}/record/{id}', 'AnyContent\Repository\Modules\Core\ContentRecords\ContentController::getOne');
        $app->get('/1/{repositoryName}/content/{contentTypeName}/record/{id}/{workspace}', 'AnyContent\Repository\Modules\Core\ContentRecords\ContentController::getOne');
        $app->get('/1/{repositoryName}/content/{contentTypeName}/record/{id}/{workspace}/{clippingName}', 'AnyContent\Repository\Modules\Core\ContentRecords\ContentController::getOne');

        // get records (additional query parameters: timeshift, language, order, properties, limit, page, subset, filter)
        $app->get('/1/{repositoryName}/content/{contentTypeName}/records', 'AnyContent\Repository\Modules\Core\ContentRecords\ContentController::getMany');
        $app->get('/1/{repositoryName}/content/{contentTypeName}/records/{workspace}', 'AnyContent\Repository\Modules\Core\ContentRecords\ContentController::getMany');
        $app->get('/1/{repositoryName}/content/{contentTypeName}/records/{workspace}/{clippingName}', 'AnyContent\Repository\Modules\Core\ContentRecords\ContentController::getMany');

        // delete record (additional query parameter: language)
        $app->delete('/1/{repositoryName}/content/{contentTypeName}/record/{id}', 'AnyContent\Repository\Modules\Core\ContentRecords\ContentController::deleteOne');
        $app->delete('/1/{repositoryName}/content/{contentTypeName}/record/{id}/{workspace}', 'AnyContent\Repository\Modules\Core\ContentRecords\ContentController::deleteOne');

        // delete records (additional query parameter: language, reset)
        $app->delete('/1/{repositoryName}/content/{contentTypeName}/records', 'AnyContent\Repository\Modules\Core\ContentRecords\ContentController::truncate');
        $app->delete('/1/{repositoryName}/content/{contentTypeName}/records/{workspace}', 'AnyContent\Repository\Modules\Core\ContentRecords\ContentController::truncate');

        // insert/update record (additional query parameters: record, language)
        $app->post('/1/{repositoryName}/content/{contentTypeName}/records', 'AnyContent\Repository\Modules\Core\ContentRecords\ContentController::post');
        $app->post('/1/{repositoryName}/content/{contentTypeName}/records/{workspace}', 'AnyContent\Repository\Modules\Core\ContentRecords\ContentController::post');
        $app->post('/1/{repositoryName}/content/{contentTypeName}/records/{workspace}/{clippingName}', 'AnyContent\Repository\Modules\Core\ContentRecords\ContentController::post');

        // sort records (additional query parameters: list, language)
        $app->post('/1/{repositoryName}/content/{contentTypeName}/sort-records', 'AnyContent\Repository\Modules\Core\ContentRecords\ContentController::sort');
        $app->post('/1/{repositoryName}/content/{contentTypeName}/sort-records/{workspace}', 'AnyContent\Repository\Modules\Core\ContentRecords\ContentController::sort');

        // simplification routes, solely for human interaction with the api
        $app->get('/1/{repositoryName}/content/{contentTypeName}', 'AnyContent\Repository\Modules\Core\ContentRecords\ContentController::getContentShortCut');

         */
    }

    public static function index(Application $app, Request $request, $repositoryName, $contentTypeName, $workspace = 'default', $viewName = 'default')
    {
        $repository = self::getRepository($app, $request);

        if ($repository->hasContentType($contentTypeName))
        {
            $repository->selectContentType($contentTypeName);
            $definition = $repository->getCurrentContentTypeDefinition();
            $dataDimensions = $repository->getCurrentDataDimensions();

            $data = [];

            $data['info']['repository']=$repository->getName();
            $data['info']['content_type']=$definition->getName();
            $data['info']['workspace']=$dataDimensions->getWorkspace();
            $data['info']['language']=$dataDimensions->getLanguage();
            $data['info']['view']=$dataDimensions->getViewName();
            $data['info']['count']=$repository->countRecords();
            $data['info']['lastchange']=$repository->getLastModifiedDate($contentTypeName);

            $data ['records']=$repository->getRecords();

            return self::getCachedJSONResponse($app, $data, $request, $repository);
        }



    }


}