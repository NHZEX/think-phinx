<?php

use HZEX\Phinx\Service;
use think\App;

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