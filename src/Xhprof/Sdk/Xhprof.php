<?php

namespace Xhprof\Sdk;

class Xhprof
{
    public $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public  function IsCollection()
    {
//        $url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $url = 'website.huidian.api.ulucu.com/recognition/face/shopping_guide/store_rank';
        $api = parse_url($url, PHP_URL_PATH);
        $this->client->GetInfo($api);
    }
}