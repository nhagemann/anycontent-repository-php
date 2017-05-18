<?php

namespace AnyContent\Service\RestLikeController;

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

class ConfigController extends AbstractController
{

    public static function init(Application $app, $path)
    {
        // get config (additional query parameters: timeshift, language)
        $app->get($path . '/{repositoryName}/config/{configTypeName}/record', __CLASS__ . '::getConfig');
        $app->get($path . '/{repositoryName}/config/{configTypeName}/record/{workspace}', __CLASS__ . '::getConfig');

        // insert/update config (additional query parameters: language)
        $app->post($path . '/{repositoryName}/config/{configTypeName}/record', __CLASS__ . '::postConfig');
        $app->post($path . '/{repositoryName}/config/{configTypeName}/record/{workspace}', __CLASS__ . '::postConfig');

        // get config shortcut
        $app->get($path . '/{repositoryName}/config/{configTypeName}', __CLASS__ . '::redirect')->value('path', $path);

        // list configs
        //$app->get('/1/{repositoryName}/config', 'AnyContent\Repository\Controller\ConfigController::index');

    }

    public static function redirect(Application $app, Request $request, $repositoryName, $configTypeName, $path)
    {
        return new RedirectResponse($path . '/' . $repositoryName . '/config/' . $configTypeName . '/record', 301);
    }

    public static function getConfig(Application $app, Request $request, $repositoryName, $configTypeName)
    {
        $repository = self::getRepository($app, $request, false);

        if ($repository->hasConfigType($configTypeName)) {
            $record = $repository->getConfig($configTypeName);

            if ($record) {

                $dataDimensions = $repository->getCurrentDataDimensions();

                $data = [];

                $data['info']['repository']  = $repository->getName();
                $data['info']['config_type'] = $configTypeName;
                $data['info']['workspace']   = $dataDimensions->getWorkspace();
                $data['info']['language']    = $dataDimensions->getLanguage();
                $data['info']['view']        = $dataDimensions->getViewName();
                $data['info']['lastchange']  = $repository->getLastModifiedDate(null, $configTypeName);

                $data['record'] = $record;

                return self::getCachedJSONResponse($app, $data, $request, $repository);
            }
        }

        throw new NotFoundException(
            'Config type ' . $configTypeName . ' not found within repository ' . $repositoryName . '.',
            Service::ERROR_404_UNKNOWN_CONFIGTYPE
        );
    }

    public static function postConfig(Application $app, Request $request, $repositoryName, $configTypeName, $workspace = 'default', $viewName = 'default')
    {
        $repository = self::getRepository($app, $request, false);

        if ($request->request->has('record')) {

            $jsonRecord = json_decode($request->request->get('record'), true);

            if ($jsonRecord) {

                if ($repository->hasConfigType($configTypeName)) {
                    $record = $repository->getRecordFactory()->createRecordFromJSON(
                        $repository->getConfigTypeDefinition($configTypeName),
                        $jsonRecord,
                        $viewName,
                        $workspace,
                        $repository->getCurrentDataDimensions()->getLanguage()
                    );

                    self::checkRecord($record, $viewName);

                    $result = $repository->saveConfig($record);

                    return new JsonResponse($result);
                }
            }
        }

        throw new BadRequestException(__CLASS__ . '_' . __METHOD__, Service::ERROR_400_BAD_REQUEST);
    }

}