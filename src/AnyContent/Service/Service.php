<?php

namespace AnyContent\Service;

use AnyContent\Client\Client;
use AnyContent\Client\Repository;
use AnyContent\Client\RepositoryFactory;
use AnyContent\Service\Exception\BadRequestException;
use AnyContent\Service\Exception\NotFoundException;
use AnyContent\Service\Exception\NotModifiedException;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Service
{

    /** @var  Application */
    protected $app;

    /** @var  Client */
    protected $client;

    protected $config;

    protected $path;

    /** @var  Repository[] */
    protected $repositories = [];

    protected $httpCache = false;

    const ERROR_400_BAD_REQUEST = 1;
    const ERROR_400_UNKNOWN_PROPERTIES = 8;

    const ERROR_404_UNKNOWN_REPOSITORY = 2;
    const ERROR_404_UNKNOWN_WORKSPACE = 20;
    const ERROR_404_UNKNOWN_LANGUAGE = 21;
    const ERROR_404_UNKNOWN_VIEW = 22;
    const ERROR_404_UNKNOWN_CONTENTTYPE = 3;
    const ERROR_404_UNKNOWN_CONFIGTYPE = 5;

    const ERROR_404_RECORD_NOT_FOUND = 4;
    const ERROR_404_FILE_NOT_FOUND = 7;

    const API_RESTLIKE_1 = 0;
    const API_REST_1 = 1;

    public function __construct(Application $app, $config, $path = '', $apiVersion = null)
    {
        $this->app = $app;

        $this->path = str_replace('//', '/', '/' . trim($path, '/'));

        $this->client = new Client();

        $this->config = $config;

        switch ($apiVersion) {
            case Service::API_RESTLIKE_1:
                $this->initRestLike1Routes();
                break;
            case Service::API_REST_1;
                throw new \Exception('Not yet implemented.');
                break;
            default:
                throw new \Exception('Not yet implemented.');
                break;
        }

        $app->after(
            function (Request $request, Response $response) {
                if ($response instanceof JsonResponse) {
                    $response->setEncodingOptions(JSON_PRETTY_PRINT);
                }

                return $response;
            }
        );

        $app->error(
            function (NotFoundException $e) use ($app) {
                return $app->json(['error' => ['code' => $e->getCode(), 'message' => $e->getMessage()]], 404);
            }
        );
        $app->error(
            function (BadRequestException $e) use ($app) {
                return $app->json(['error' => ['code' => $e->getCode(), 'message' => $e->getMessage()]], 400);
            }
        );
        $app->error(
            function (NotModifiedException $e) use ($app) {

                $response = new JsonResponse(null, 304, array('X-Status-Code' => 304));
                $response->setEtag($e->getEtag());
                $response->setPublic();
                $response->setNotModified();

                return $response;
            }
        );
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Client $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    protected function initRestLike1Routes()
    {
        \AnyContent\Service\RestLikeController\InfoController::init($this->app, $this->path);
        \AnyContent\Service\RestLikeController\CMDLController::init($this->app, $this->path);
        \AnyContent\Service\RestLikeController\ContentController::init($this->app, $this->path);
        \AnyContent\Service\RestLikeController\ConfigController::init($this->app, $this->path);
        \AnyContent\Service\RestLikeController\FilesController::init($this->app, $this->path);
    }

    public function getRepository($repositoryName)
    {
        if (array_key_exists($repositoryName, $this->repositories)) {
            return $this->repositories[$repositoryName];
        }

        if (array_key_exists($repositoryName, $this->config)) {
            $repositoryFactory = new RepositoryFactory();
            $repository        = $repositoryFactory->createRepositoryFromConfigArray(
                $repositoryName,
                $this->config[$repositoryName]
            );
            $this->client->addRepository($repository);
            $this->repositories[$repositoryName] = $repository;

            return $repository;
        }

        throw new NotFoundException('Unknown repository ' . $repositoryName, self::ERROR_404_UNKNOWN_REPOSITORY);
    }
}
