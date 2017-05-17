<?php

namespace AnyContent\Service\V1Controller;

use AnyContent\Client\Record;
use AnyContent\Service\Exception\BadRequestException;
use AnyContent\Service\Exception\NotFoundException;
use AnyContent\Service\Service;
use CMDL\CMDLParserException;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentController extends AbstractController
{

    public static function init(Application $app)
    {

        // get record (additional query parameters: timeshift, language)
        $app->get('/1/{repositoryName}/content/{contentTypeName}/record/{id}', __CLASS__ . '::getRecord');
        $app->get('/1/{repositoryName}/content/{contentTypeName}/record/{id}/{workspace}', __CLASS__ . '::getRecord');
        $app->get(
            '/1/{repositoryName}/content/{contentTypeName}/record/{id}/{workspace}/{viewName}',
            __CLASS__ . '::getRecord'
        );

        // get records (additional query parameters: timeshift, language, order, properties, limit, page, subset, filter)
        $app->get('/1/{repositoryName}/content/{contentTypeName}/records', __CLASS__ . '::index');
        $app->get('/1/{repositoryName}/content/{contentTypeName}/records/{workspace}', __CLASS__ . '::index');
        $app->get(
            '/1/{repositoryName}/content/{contentTypeName}/records/{workspace}/{viewName}',
            __CLASS__ . '::index'
        );

        // insert/update record (additional query parameters: record/records, language)
        $app->post('/1/{repositoryName}/content/{contentTypeName}/records', __CLASS__ . '::postRecords');
        $app->post('/1/{repositoryName}/content/{contentTypeName}/records/{workspace}', __CLASS__ . '::postRecords');
        $app->post(
            '/1/{repositoryName}/content/{contentTypeName}/records/{workspace}/{viewName}',
            __CLASS__ . '::postRecords'
        );

        // delete record (additional query parameter: language)
        $app->delete('/1/{repositoryName}/content/{contentTypeName}/record/{id}', __CLASS__ . '::deleteRecord');
        $app->delete(
            '/1/{repositoryName}/content/{contentTypeName}/record/{id}/{workspace}',
            __CLASS__ . '::deleteRecord'
        );

        // delete records (additional query parameter: language)
        $app->delete('/1/{repositoryName}/content/{contentTypeName}/records', __CLASS__ . '::deleteRecords');
        $app->delete('/1/{repositoryName}/content/{contentTypeName}/records/{workspace}', __CLASS__ . '::deleteRecords');

        // get records shortcut
        $app->get('/1/{repositoryName}/content/{contentTypeName}', __CLASS__ . '::redirect');
        /*
         *  // list content
        $app->get('/1/{repositoryName}/content', 'AnyContent\Repository\Modules\Core\ContentRecords\ContentController::index');







        // sort records (additional query parameters: list, language)
        $app->post('/1/{repositoryName}/content/{contentTypeName}/sort-records', 'AnyContent\Repository\Modules\Core\ContentRecords\ContentController::sort');
        $app->post('/1/{repositoryName}/content/{contentTypeName}/sort-records/{workspace}', 'AnyContent\Repository\Modules\Core\ContentRecords\ContentController::sort');


         */
    }

    public static function index(Application $app, Request $request, $repositoryName, $contentTypeName, $workspace = 'default', $viewName = 'default')
    {
        $repository = self::getRepository($app, $request);

        if ($repository->hasContentType($contentTypeName)) {
            $repository->selectContentType($contentTypeName);
            $definition     = $repository->getCurrentContentTypeDefinition();
            $dataDimensions = $repository->getCurrentDataDimensions();

            $data = [];

            $data['info']['repository']   = $repository->getName();
            $data['info']['content_type'] = $definition->getName();
            $data['info']['workspace']    = $dataDimensions->getWorkspace();
            $data['info']['language']     = $dataDimensions->getLanguage();
            $data['info']['view']         = $dataDimensions->getViewName();
            $data['info']['count']        = $repository->countRecords();
            $data['info']['lastchange']   = $repository->getLastModifiedDate($contentTypeName);

            $page  = $request->query->get('page', 1);
            $count = $request->query->get('count', null);

            $order = '.id';
            if ($request->query->has('order')) {
                $order = $request->query->get('order');

                if ($order == 'property' && $request->query->has('properties')) {
                    $order = $request->query->get('properties');
                }

                $order = str_replace('+', '', $order);

                // Old order style
                $map = [
                    'id'        => '.id',
                    'id-'       => '.id-',
                    'pos'       => 'position',
                    'pos-'      => 'position-',
                    'creation'  => '.info.creation.timestamp',
                    'creation-' => '.info.creation.timestamp-',
                    'change'    => '.info.lastchange.timestamp',
                    'change-'   => '.info.lastchange.timestamp-',
                ];

                if (array_key_exists($order, $map)) {
                    $order = $map[$order];
                }

                $order = explode(',', $order);
            }

            $filter = $request->query->get('filter', '');
            $filter = str_replace('><', '*=', (string)$filter);

            $data ['records'] = $repository->getRecords($filter, $order, $page, $count);

            return self::getCachedJSONResponse($app, $data, $request, $repository);
        }
    }

    public static function redirect(Application $app, Request $request, $repositoryName, $contentTypeName)
    {
        return new RedirectResponse('/1/' . $repositoryName . '/content/' . $contentTypeName . '/records', 301);
    }

    public static function getRecord(Application $app, Request $request, $repositoryName, $contentTypeName, $id, $workspace = 'default', $viewName = 'default')
    {
        $repository = self::getRepository($app, $request);

        $record = $repository->getRecord($id);

        if ($record) {

            $dataDimensions = $repository->getCurrentDataDimensions();

            $data = [];

            $data['info']['repository']   = $repository->getName();
            $data['info']['content_type'] = $contentTypeName;
            $data['info']['workspace']    = $dataDimensions->getWorkspace();
            $data['info']['language']     = $dataDimensions->getLanguage();
            $data['info']['view']         = $dataDimensions->getViewName();
            $data['info']['count']        = $repository->countRecords();
            $data['info']['lastchange']   = $repository->getLastModifiedDate($contentTypeName);

            $data['record'] = $record;

            return self::getCachedJSONResponse($app, $data, $request, $repository);
        }

        throw new NotFoundException(
            'Record with id ' . $id . ' not found for content type ' . $contentTypeName . ' within repository ' . $repositoryName . '.',
            Service::ERROR_404_RECORD_NOT_FOUND
        );
    }

    public static function postRecords(Application $app, Request $request, $repositoryName, $contentTypeName, $workspace = 'default', $viewName = 'default')
    {
        $repository = self::getRepository($app, $request);

        if ($request->request->has('record')) {

            $jsonRecord = json_decode($request->request->get('record'), true);

            if ($jsonRecord) {

                $record = $repository->getRecordFactory()->createRecordFromJSON(
                    $repository->getCurrentContentTypeDefinition(),
                    $jsonRecord,
                    $viewName,
                    $workspace,
                    $repository->getCurrentDataDimensions()->getLanguage()
                );

                self::checkRecord($record, $viewName);

                $id = $repository->saveRecord($record);

                return new JsonResponse($id);
            }
        }

        if ($request->request->has('records')) {

            $jsonRecords = json_decode($request->request->get('records'), true);

            if ($jsonRecords && is_array($jsonRecords)) {

                $records = [];
                foreach ($jsonRecords as $jsonRecord) {

                    $record = $repository->getRecordFactory()->createRecordFromJSON(
                        $repository->getCurrentContentTypeDefinition(),
                        $jsonRecord,
                        $viewName,
                        $workspace,
                        $repository->getCurrentDataDimensions()->getLanguage()
                    );

                    self::checkRecord($record, $viewName);

                    $records[] = $record;
                }

                $ids = $repository->saveRecords($records);

                return new JsonResponse($ids);
            }
        }

        throw new BadRequestException(__CLASS__ . '_' . __METHOD__, Service::ERROR_400_BAD_REQUEST);
    }



    public static function deleteRecord(Application $app, Request $request, $repositoryName, $contentTypeName, $id, $workspace = 'default')
    {
        $repository = self::getRepository($app, $request);
        $repository->deleteRecord($id);

        return new JsonResponse(true);
    }

    public static function deleteRecords(Application $app, Request $request, $repositoryName, $contentTypeName, $workspace = 'default')
    {
        $repository = self::getRepository($app, $request);

        $repository->deleteAllRecords();

        return new JsonResponse(true);
    }
}