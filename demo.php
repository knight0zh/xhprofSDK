<?php
/**
 * Created by PhpStorm.
 * User: knight0zh
 * Date: 2019/9/18
 * Time: 下午5:25
 */
require_once './vendor/autoload.php';
use Xhprof\Sdk\Xhprof;

$x = new Xhprof();
$x->IsCollection();