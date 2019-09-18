<?php

namespace Xhprof\Sdk;

class Client
{
    public $client;
    private $config;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client();
        $this->config = require_once __DIR__.'/../config.php';
    }

    public function GetInfo(string $api)
    {
        $response = $this->client->request('GET', $this->config['server'].'/api/info', [
            'query' => ['api' => $api],
        ]);
        print_r($response->getBody()->getContents());
        die;
    }

}