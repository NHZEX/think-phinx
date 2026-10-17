# Think-Phinx

为 ThinkPHP 6 / 8 提供 Phinx 数据迁移和 seed 命令。

[![Latest Stable Version](https://poser.pugx.org/nhzex/think-phinx/v/stable)](https://packagist.org/packages/nhzex/think-phinx)
[![License](https://poser.pugx.org/nhzex/think-phinx/license)](https://packagist.org/packages/nhzex/think-phinx)
[![workflows](https://github.com/NHZEX/think-phinx/actions/workflows/upgrade-validation.yml/badge.svg?branch=5.0)](https://github.com/NHZEX/think-phinx/actions)
[![coverage](https://codecov.io/gh/nhzex/think-phinx/graph/badge.svg)](https://codecov.io/gh/nhzex/think-phinx)

## 安装

5.0 尚未发布 tag，试用当前开发分支：

```sh
composer require nhzex/think-phinx:5.0.x-dev
```

需要 64 位 PHP 8.1+、Composer 2.1+；使用 Phinx `~0.16.12`（允许 0.16 系列补丁更新）、Symfony 6.4。升级前备份数据库，并在测试库验证迁移与回滚；依赖变化和兼容范围见 [5.0 升级说明](UPGRADE-5.0.md)。

## 使用

```sh
php think migrate:create CreateUsers
php think migrate:run
php think migrate:status
php think migrate:rollback
php think seed:create UserSeeder
php think seed:run
```

另有 `migrate:breakpoint` 管理断点、`migrate:test` 检查配置；各命令选项可通过 `php think <命令> --help` 查看。若升级后命令未出现，运行 `php think service:discover`。

## 配置

在 `config/phinx.php` 中配置路径和迁移记录表，确保指定目录存在且可读：

```php
<?php
return [
    'paths' => [
        'migrations' => [
            'DbMigrations' => 'database/migrations',
        ],
        'seeds' => [
            'DbSeeds' => 'database/seeds'
        ]
    ],
    'environments' => [
        'default_migration_table' => '_phinxlog',
    ],
    'version_order' => 'creation'
];

```

## 文档

- [Phinx 官方文档](https://book.cakephp.org/phinx)
- [Phinx 中文文档](https://tsy12321.gitbooks.io/phinx-doc/content)
- [升级、兼容性与测试](UPGRADE-5.0.md)
