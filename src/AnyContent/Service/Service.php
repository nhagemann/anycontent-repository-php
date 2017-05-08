<?php

namespace AnyContent\Service;

use AnyContent\Client\Client;
use AnyContent\Client\Repository;
use AnyContent\Client\RepositoryFactory;
use AnyContent\Service\Exception\BadRequestException;
use AnyContent\Service\Exception\NotFoundException;
use AnyContent\Service\Exception\NotModifiedException;
use AnyContent\Service\V1Controller\CMDLController;
use AnyContent\Service\V1Controller\ContentController;
use AnyContent\Service\V1Controller\InfoController;
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

    /** @var  Repository[] */
    protected $repositories = [];

    protected $httpCache = false;

    const ERROR_400_BAD_REQUEST = 1;
    const ERROR_400_UNKNOWN_PROPERTIES = 8;

    const ERROR_404_UNKNOWN_REPOSITORY = 2;
    const ERROR_404_UNKNOWN_CONTENTTYPE = 3;
    const ERROR_404_RECORD_NOT_FOUND = 4;
    const ERROR_404_UNKNOWN_CONFIGTYPE = 5;
    const ERROR_404_CONFIG_NOT_FOUND = 6;
    const ERROR_404_FILE_NOT_FOUND = 7;

    const ERROR_404_UNKNOWN_WORKSPACE = 20;
    const ERROR_404_UNKNOWN_LANGUAGE = 21;
    const ERROR_404_UNKNOWN_VIEW = 22;


//    const BAD_REQUEST                 = 1;
//    const UNKNOWN_REPOSITORY          = 2;
//    const UNKNOWN_CONTENTTYPE         = 3;
//    const RECORD_NOT_FOUND            = 4;
//    const UNKNOWN_CONFIGTYPE          = 5;
//    const CONFIG_NOT_FOUND            = 6;
//    const FILE_NOT_FOUND              = 7;
//    const UNKNOWN_PROPERTY            = 8;
//    const UNKNOWN_ERROR               = 9;
//    const MISSING_MANDATORY_PARAMETER = 10;
//    const SERVER_ERROR                = 11;

    public function __construct(Application $app, $config)
    {
        $this->app = $app;

        $this->client = new Client();

        $this->config = $config;


        $this->initV1Routes();

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
            function (NotModifiedException $e) {
                $response = new JsonResponse();
                $response->setEtag($e->getEtag());
                $response->setPublic();

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


    protected function initV1Routes()
    {
        InfoController::init($this->app);
        CMDLController::init($this->app);
        ContentController::init($this->app);


    }


    public function getRepository($repositoryName)
    {
        if (array_key_exists($repositoryName, $this->repositories)) {
            return $this->repositories[$repositoryName];
        }

        if (array_key_exists($repositoryName, $this->config)) {
            $repositoryFactory = new RepositoryFactory();
            $repository = $repositoryFactory->createRepositoryFromConfigArray(
                $repositoryName,
                $this->config[$repositoryName]
            );
            $this->client->addRepository($repository);
            $this->repositories[$repositoryName] = $repository;

            return $repository;
        }

        throw new NotFoundException('Unknown repository '.$repositoryName, self::ERROR_404_UNKNOWN_REPOSITORY);
    }
}
