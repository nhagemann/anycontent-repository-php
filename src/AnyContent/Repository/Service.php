<?php

namespace AnyContent\Repository;

use AnyContent\Client\Client;
use AnyContent\Client\Repository;
use Silex\Application;

class Service extends Application
{
    /** @var  Client */
    protected $client;

    public function __construct(array $values = array())
    {
        parent::__construct();

        $this->get('/2/{repositoryName}/info', 'AnyContent\Repository\Controller\Info::defaultAction');
    }


    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    public function getClient()
    {
        return $this->client;
    }


    /**
     * @param $repositoryName
     * @return Repository
     * @throws \AnyContent\AnyContentClientException
     */
    public function getRepository($repositoryName)
    {
        return $this->client->getRepository($repositoryName);

    }

}