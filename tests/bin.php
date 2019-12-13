#!/usr/bin/env php
<?php

use think\App;

require __DIR__ . '/../vendor/autoload.php';
require 'phpunit-bootstrap.php';

$app = App::getInstance();
$app->console->run();