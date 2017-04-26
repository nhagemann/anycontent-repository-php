<?php

namespace AnyContent\Service;

use AnyContent\Client\Client;
use AnyContent\Client\Repository;
use AnyContent\Client\RepositoryFactory;
use AnyContent\Service\Exception\BadRequestException;
use AnyContent\Service\Exception\NotFoundException;
use AnyContent\Service\Exception\NotModifiedException;
use AnyContent\Service\V1Controller\InfoController;
use Silex\Application;
use Silex\Provider\HttpCacheServiceProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

class Service extends Application
{

    /** @var  Client */
    protected $client;

    protected $config;

    /** @var  Repository[] */
    protected $repositories = [];

    protected $httpCache = false;


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


    public function enableHTTPCache($path)
    {
        $this->register(new HttpCacheServiceProvider(), array(
            'http_cache.cache_dir' => $path,
        ));
        $this->httpCache = true;

    }


    public function run(Request $request = null)
    {
        $this->initRepositories();
        $this->initV1Routes();

        $this->after(function (Request $request, Response $response) {
            if ($response instanceof JsonResponse) {
                $response->setEncodingOptions(JSON_PRETTY_PRINT);
            }

            return $response;
        });

        $this->error(function (NotFoundException $e) {
            return $this->json(['error' => ['code' => 2, 'message' => $e->getMessage()]], 404);
        });
        $this->error(function (BadRequestException $e) {
            return $this->json(['error' => ['code' => $e->getCode(), 'message' => $e->getMessage()]], 400);
        });
        $this->error(function (NotModifiedException $e) {
            $response = new JsonResponse();
            $response->setEtag($e->getEtag());
            $response->setPublic();

            return $response;
        });

        if ($this->httpCache) {
            $this['http_cache']->run();
        } else {
            parent::run($request);
        }
    }


    protected function initRepositories()
    {
        $this->client = new Client();

        $this->config = Yaml::parse(file_get_contents(APPLICATION_PATH . '/config/config.yml'));

    }


    protected function initV1Routes()
    {
        InfoController::init($this);

    }


    public function getRepository($repositoryName)
    {
        if (array_key_exists($repositoryName, $this->repositories)) {
            return $this->repositories[$repositoryName];
        }

        if (array_key_exists($repositoryName, $this->config)) {
            $repositoryFactory = new RepositoryFactory();
            $repository        = $repositoryFactory->createRepositoryFromConfigArray($repositoryName,
                $this->config[$repositoryName]);
            $this->client->addRepository($repository);
            $this->repositories[$repositoryName] = $repository;

            return $repository;
        }

        throw new \Exception('Unknown repository ' . $repositoryName);
    }
}
