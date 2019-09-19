<?php

namespace Xhprof\Sdk;

use GuzzleHttp\Exception\RequestException;

class Client
{
    public $client;
    private $config;

    public function __construct(array $config)
    {
        $this->client = new \GuzzleHttp\Client();
        $this->config = $config;
    }

    public function GetInfo(string $api)
    {
        try {
            $response = $this->client->request('GET', $this->config['server'].'/api/info', [
                'query' => ['api' => $api],
            ]);
        } catch (RequestException $e) {
            return 0;
        }
        if ($response->getStatusCode() != '200') {
            return 0;
        }
        $response = json_decode($response->getBody()->getContents(), true);

        return empty($response['data']) ? 0 : $response['data'];
    }

}