<?php

use think\App;
use Zxin\Think\Phinx\Service;

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

// Initialize the console and framework helpers without printing to PHPUnit output.
$app->console->setAutoExit(false);
