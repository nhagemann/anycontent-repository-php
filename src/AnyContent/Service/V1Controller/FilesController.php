<?php

namespace AnyContent\Service\V1Controller;

use AnyContent\Service\Service;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class FilesController extends AbstractController
{

    public static function init(Application $app)
    {
        // get binary file
        $app->get('/1/{repositoryName}/file/{path}', __CLASS__.'::getFile')
            ->assert('path', '.+');

        // list files
        $app->get('/1/{repositoryName}/files', __CLASS__.'::listFiles');
        $app->get('/1/{repositoryName}/files/', __CLASS__.'::listFiles');
        $app->get('/1/{repositoryName}/files/{path}', __CLASS__.'::listFiles')
            ->assert('path', '.+');

        // save file (post body contains binary)
        $app->post('/1/{repositoryName}/file/{path}', __CLASS__.'::postFile')
            ->assert('path', '.+');

        // create folder
        $app->post('/1/{repositoryName}/files/{path}', __CLASS__.'::createFolder')
            ->assert('path', '.+');

        // delete file
        $app->delete('/1/{repositoryName}/file/{path}', __CLASS__.'::deleteFile')
            ->assert('path', '.+');

        // delete files
        $app->delete('/1/{repositoryName}/files/{path}', __CLASS__.'::deleteFolder')
            ->assert('path', '.+');
        $app->delete('/1/{repositoryName}/files', __CLASS__.'::index');
        $app->delete('/1/{repositoryName}/files/', __CLASS__.'::index');
    }


    public static function listFiles(
        Application $app,
        Request $request,
        $repositoryName,
        $path = ''
    ) {
        $repository = self::getRepository($app, $request);

        $fileManager = $repository->getFileManager();


        $folder = $fileManager->getFolder($path);

        return new JsonResponse($folder);
    }
}