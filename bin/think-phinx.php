#!/usr/bin/env php
<?php

namespace think;

// 加载基础文件
require __DIR__ . '/../vendor/autoload.php';

// 应用初始化
/** @noinspection PhpUnhandledExceptionInspection */
(new App())->console->run();
