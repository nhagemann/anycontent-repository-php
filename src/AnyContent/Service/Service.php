<?php

namespace AnyContent\Service;

use AnyContent\Client\Client;
use AnyContent\Client\Repository;
use AnyContent\Client\RepositoryFactory;
use AnyContent\Service\Exception\BadRequestException;
use AnyContent\Service\Exception\NotFoundException;
use AnyContent\Service\Exception\NotModifiedException;
use AnyContent\Service\V1Controller\ContentController;
use AnyContent\Service\V1Controller\InfoController;
use Silex\Application;
use Silex\Provider\HttpCacheServiceProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

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

    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->initRepositories();
        $this->initV1Routes();

        $app->after(function (Request $request, Response $response) {
            if ($response instanceof JsonResponse) {
                $response->setEncodingOptions(JSON_PRETTY_PRINT);
            }

            return $response;
        });

        $app->error(function (NotFoundException $e) use ($app) {
            return  $app->json(['error' => ['code' => 2, 'message' => $e->getMessage()]], 404);
        });
        $app->error(function (BadRequestException $e) use ($app) {
            return  $app->json(['error' => ['code' => $e->getCode(), 'message' => $e->getMessage()]], 400);
        });
        $app->error(function (NotModifiedException $e) {
            $response = new JsonResponse();
            $response->setEtag($e->getEtag());
            $response->setPublic();

            return $response;
        });


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



    protected function initRepositories()
    {
        $this->client = new Client();

        $this->config = Yaml::parse(file_get_contents(APPLICATION_PATH . '/config/config.yml'));

    }


    protected function initV1Routes()
    {
        InfoController::init($this->app);
        ContentController::init($this->app);

    }


    public function getRepository($repositoryName)
    {
        if (array_key_exists($repositoryName, $this->repositories)) {
            return $this->repositories[$repositoryName];
        }

        if (array_key_exists($repositoryName, $this->config['repositories'])) {
            $repositoryFactory = new RepositoryFactory();
            $repository        = $repositoryFactory->createRepositoryFromConfigArray($repositoryName,
                $this->config['repositories'][$repositoryName]);
            $this->client->addRepository($repository);
            $this->repositories[$repositoryName] = $repository;

            return $repository;
        }

        throw new NotFoundException('Unknown repository ' . $repositoryName);
    }
}
