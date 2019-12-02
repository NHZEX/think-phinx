<?php

use HZEX\Phinx\Service;
use think\App;

require __DIR__ . '/TestConfiguration.php';

$app = new App(__DIR__);
$app->register(Service::class);
$app->console;