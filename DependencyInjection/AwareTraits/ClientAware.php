<?php


namespace SunValley\TaskManager\Symfony\DependencyInjection\AwareTraits;


use SunValley\TaskManager\Client;

trait ClientAware
{
    /** @var Client */
    private $client;

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param Client $client
     */
    public function setClient(Client $client): void
    {
        $this->client = $client;
    }
}