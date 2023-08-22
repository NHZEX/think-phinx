<?php

use think\App;
use Zxin\Think\Phinx\Service;

//require __DIR__ . '/../third-party-build/vendor/scoper-autoload.php';
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/TestConfiguration.php';

$app = new App(__DIR__);
$app->register(Service::class);
$app->config->set([
    'default'         => 'file',
    'stores'  => [
        'file' => [
            'type'       => 'File',
            'path'       => '/tmp/cache',
        ],
    ],
], 'cache');
var_dump($app->console->getLongVersion());