# Think-Phinx
thinkphp 6.0, 8.0 phinx 数据迁移

[![Latest Stable Version](https://poser.pugx.org/nhzex/think-phinx/v/stable)](https://packagist.org/packages/nhzex/think-phinx)
[![License](https://poser.pugx.org/nhzex/think-phinx/license)](https://packagist.org/packages/nhzex/think-phinx)
[![workflows](https://github.com/NHZEX/think-phinx/actions/workflows/upgrade-validation.yml/badge.svg?branch=5.0)](https://github.com/NHZEX/think-phinx/actions)
[![coverage](https://codecov.io/gh/nhzex/think-phinx/graph/badge.svg)](https://codecov.io/gh/nhzex/think-phinx)

## Installation
composer require nhzex/think-phinx

## 重大变更

5.0 使用 Composer 正常安装 `robmorgan/phinx:0.16.12`，不再内置依赖副本、使用 PHP-Scoper 或通过 `replace` 声明提供 Phinx/CakePHP。

- 需要 64 位 PHP 8.1 或更高版本，以及 Composer 2.1+。
- `topthink/think-helper` 最低 3.1.2，避免最低依赖解析到与 ORM 类型签名不兼容的旧 Jsonable 接口。
- 配套库最低版本为 `zxin/phinx-schema:^2.1.1`、`zxin/think-symfony-bridge:^3.1`，Symfony Config/Console 限定 6.4 LTS（现有 bridge 的接口签名不兼容 Console 7）。
- 保留 `migrate:*` / `seed:*` 命令、`Zxin\Think\Phinx` 和旧 `HZEX\Phinx\Schema` API。
- 使用上游 Phinx/CakePHP 命名空间，删除的内部 `third-party` 路径不属于业务 API。默认迁移和 seed 模板从 Composer 实际安装目录读取，支持自定义 `vendor-dir`。
- Phinx 0.16.12 使用 CakePHP 5 依赖；不再自动加载旧 CakePHP 的全局辅助函数。独立进程测试检查 `env()` 来自 ThinkPHP，并检查 Phinx/CakePHP 是否引入全局函数，同时验证已有 `env()` 不受影响。CakePHP 仍会定义 `DS`、`CAKE_DATE_RFC7231` 常量。
- 显式限制 CakePHP Database 为 `^5.2.15`，排除 `>=5.3 <5.3.7`，避免解析到 [GHSA-vjqc-q4mp-2rvf](https://github.com/advisories/GHSA-vjqc-q4mp-2rvf) 已知受影响版本；不要关闭 Composer 的安全检查。
- 升级前备份数据库，检查项目其他 Phinx/CakePHP 依赖约束，并在测试数据库上验证已有迁移的执行与回滚。上游 Phinx 的行为变化仍需由应用测试覆盖。

| 版本 | Phinx | 依赖方式 |
|:---:|:---:|:---:|
| 4.0.* | 0.13.4 | 内置 bundle |
| 4.1.* | 0.14.0 | 内置 bundle |
| 5.0 | 0.16.12 | 普通 Composer 依赖 |

## Use
```
 migrate
  migrate:breakpoint  Manage breakpoints
  migrate:create      Create a new migration
  migrate:rollback    Rollback the last or to a specific migration
  migrate:run         Migrate the database
  migrate:status      Show migration status
  migrate:test        Verify the configuration file
 seed
  seed:create         Create a new database seeder
  seed:run            Run database seeders
``` 

## Config
请确保配置文件中指定的目录存在且可读
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

## 注意事项

1. 如果升级到`4.x`后执行命令行提示不存在请手动执行`./think service:discover`以重新发现服务。

## Doc
- [Phinx EN](https://book.cakephp.org/phinx)
- [Phinx CN](https://tsy12321.gitbooks.io/phinx-doc/content)
- [Phinx Old](http://docs.phinx.org/en/latest)

## 开发与测试

```sh
composer install
composer test:autoload
composer test -- --filter SqliteLifecycleTest
composer test
```

测试需要 `pdo_sqlite`；完整套件还需要 MySQL 和 `pdo_mysql`，数据库连接通过 `TESTS_DB_MYSQL_*` 环境变量配置（参见 `phpunit.xml.dist`）。完整套件会删除并重建测试数据库，务必使用专用测试数据库，不要指向业务数据。

CI 在 PHP 8.1–8.4 上验证最新依赖，并在 PHP 8.1 上验证最低允许依赖，覆盖模板生成、迁移、状态、断点、回滚、schema 和 seed，以及 Composer autoload 全局函数隔离。
