<?php

namespace Xhprof\Sdk;

class Xhprof
{
    public $client;
    private $config;

    public function __construct()
    {
        $this->config = require_once __DIR__.'/../config.php';
        $this->client = new Client($this->config);
    }

    private function IsCollection()
    {
        //        $url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $url = 'website.huidian.api.ulucu.com/recognition/face/shopping_guide/store_rank';
        $api = parse_url($url, PHP_URL_PATH);
        $xRate = $this->client->GetInfo($api);
        if ($xRate === 0 || $xRate > 100) {
            return false;
        }

        return mt_rand(1, 100) <= $xRate ;
    }

    public function IsXhprof()
    {
        if ( ! extension_loaded('tideways_xhprof')) {
            error_log('tideways must be loaded');
            return;
        }
        // 如果是运行脚本不采集
        $sapi_type = php_sapi_name();
        if (substr($sapi_type, 0, 3) == 'cgi' || substr($sapi_type, 0, 3) == 'cli') {
            return;
        }

        if (!$this->IsCollection()){
            return;
        }

        tideways_xhprof_enable(
            TIDEWAYS_XHPROF_FLAGS_MEMORY
            | TIDEWAYS_XHPROF_FLAGS_MEMORY_MU
            | TIDEWAYS_XHPROF_FLAGS_MEMORY_PMU
        //    | TIDEWAYS_XHPROF_FLAGS_CPU // 比较耗cpu性能,暂时不启用
        );

        ini_set('display_errors', 0);
        register_shutdown_function(function () {
            $data['profile'] = tideways_xhprof_disable();
            // ignore_user_abort(true) allows your PHP script to continue executing, even if the user has terminated their request.
            // Further Reading: http://blog.preinheimer.com/index.php?/archives/248-When-does-a-user-abort.html
            // flush() asks PHP to send any data remaining in the output buffers. This is normally done when the script completes, but
            // since we're delaying that a bit by dealing with the xhprof stuff, we'll do it now to avoid making the user wait.
            ignore_user_abort(true);
            flush();
            $uri = array_key_exists('REQUEST_URI', $_SERVER) ? $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] : null;
            if (empty($uri) && isset($_SERVER['argv'])) {
                $cmd = basename($_SERVER['argv'][0]);
                $uri = $cmd.' '.implode(' ', array_slice($_SERVER['argv'], 1));
            }
            $time = array_key_exists('REQUEST_TIME', $_SERVER) ? $_SERVER['REQUEST_TIME'] : time();
            $requestTimeFloat = explode('.', $_SERVER['REQUEST_TIME_FLOAT']);
            if ( ! isset($requestTimeFloat[1])) {
                $requestTimeFloat[1] = 0;
            }
            $requestTs = new \MongoDB\BSON\UTCDateTime($_SERVER['REQUEST_TIME']);
            $requestTsMicro = new \MongoDB\BSON\UTCDateTime($_SERVER['REQUEST_TIME_FLOAT']);
            $data['meta'] = [
                'url'              => $uri,
                'SERVER'           => $_SERVER,
                'get'              => $_GET,
                'env'              => $_ENV,
                'simple_url'       => preg_replace('/\=\d+/', '', $uri),
                'request_ts'       => $requestTs,
                'request_ts_micro' => $requestTsMicro,
                'request_date'     => date('Y-m-d', $time),
            ];
            try {
                $this->config += ['db.options' => array()];
                $mongo = new \MongoClient($this->config['db.host'], $this->config['db.options']);
                $mongo->{$this->config['db.db']}->results->insert($data);
            } catch (Exception $e) {
                error_log('xhgui - '.$e->getMessage());
            }

        });

    }
}