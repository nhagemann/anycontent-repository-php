<?php

namespace AnyContent\Service\V1Controller;

use AnyContent\Service\Exception\NotFoundException;
use AnyContent\Service\Service;
use Silex\Application;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FilesController extends AbstractController
{

    public static function init(Application $app)
    {
        // get binary file
        $app->get('/1/{repositoryName}/file/{fileId}', __CLASS__ . '::getFile')
            ->assert('fileId', '.+');

        // list files
        $app->get('/1/{repositoryName}/files', __CLASS__ . '::listFiles');
        $app->get('/1/{repositoryName}/files/', __CLASS__ . '::listFiles');
        $app->get('/1/{repositoryName}/files/{path}', __CLASS__ . '::listFiles')
            ->assert('path', '.+');

        // save file (post body contains binary)
        $app->post('/1/{repositoryName}/file/{fileId}', __CLASS__ . '::postFile')
            ->assert('fileId', '.+');

        // delete file
        $app->delete('/1/{repositoryName}/file/{fileId}', __CLASS__ . '::deleteFile')
            ->assert('fileId', '.+');

        // delete folder
        $app->delete('/1/{repositoryName}/files/{path}', __CLASS__ . '::deleteFolder')
            ->assert('path', '.+');
        $app->delete('/1/{repositoryName}/files', __CLASS__ . '::index');
        $app->delete('/1/{repositoryName}/files/', __CLASS__ . '::index');

        // create folder
        $app->post('/1/{repositoryName}/files/{path}', __CLASS__ . '::createFolder')
            ->assert('path', '.+');
    }

    public static function listFiles(Application $app, Request $request, $path = '')
    {
        $repository = self::getRepository($app, $request, false, false);

        $fileManager = $repository->getFileManager();

        $folder = $fileManager->getFolder($path);

        return new JsonResponse($folder);
    }

    public static function getFile(Application $app, Request $request, $fileId)
    {

        $repository = self::getRepository($app, $request, false, false);

        $fileManager = $repository->getFileManager();

        $file = $fileManager->getFile($fileId);

        if ($file) {

            $etag = '"' . md5($file->getTimestampLastChange() . '#' . $request->getUri()) . '"';

            $response = new Response();
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Content-Length', $file->getSize());
            $response->setPublic();
            $response->setEtag($etag);

            if (self::checkEtag($request, $etag)) {
                $response->setNotModified();

                return $response;
            }

            $response->setContent($fileManager->getBinary($file));

            return $response;
        }

        throw new NotFoundException(
            'File not found.',
            Service::ERROR_404_FILE_NOT_FOUND
        );
    }

    public static function postFile(Application $app, Request $request, $fileId)
    {

        $repository = self::getRepository($app, $request, false, false);

        $fileManager = $repository->getFileManager();

        $binary = $request->getContent();

        return new JsonResponse($fileManager->saveFile($fileId, $binary));
    }

    public static function deleteFile(Application $app, Request $request, $fileId)
    {
        $repository = self::getRepository($app, $request, false, false);

        $fileManager = $repository->getFileManager();

        return new JsonResponse($fileManager->deleteFile($fileId));
    }

    public static function deleteFolder(Application $app, Request $request, $path)
    {
        $repository = self::getRepository($app, $request, false, false);

        $fileManager = $repository->getFileManager();

        return new JsonResponse($fileManager->deleteFolder($path,true));
    }

    public static function createFolder(Application $app, Request $request, $path)
    {
        $repository = self::getRepository($app, $request, false, false);

        $fileManager = $repository->getFileManager();

        return new JsonResponse($fileManager->createFolder($path));
    }
}