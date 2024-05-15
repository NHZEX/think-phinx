# Think-Phinx
thinkphp 6.0, 8.0 phinx 数据迁移

[![Latest Stable Version](https://poser.pugx.org/nhzex/think-phinx/v/stable)](https://packagist.org/packages/nhzex/think-phinx)
[![License](https://poser.pugx.org/nhzex/think-phinx/license)](https://packagist.org/packages/nhzex/think-phinx)
[![workflows](https://github.com/nhzex/think-phinx/workflows/ci/badge.svg)](https://github.com/NHZEX/think-phinx/actions)
[![coverage](https://codecov.io/gh/nhzex/think-phinx/graph/badge.svg)](https://codecov.io/gh/nhzex/think-phinx)

## Installation
composer require nhzex/think-phinx

## 重大变更

> 自 4.0 起采用捆绑方式打包 phinx，不再污染全局环境，并内置 cakephp 依赖项，解决全局函数被污染问题！  
> 
> ~~目前版本的`phinx`将导致`env`函数被覆盖。如果使用到该函数，请在`composer`加载前重新声明。([phinx#1647](https://github.com/cakephp/phinx/issues/1647))~~  

|   版本   | phinx  | 状态  |
|:------:|:------:|:---:|
| v4.0.* | 0.13.4 | 已发布 |
| v4.1.* | 0.14.0 | RC  |

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

## Doc
- [Phinx EN](https://book.cakephp.org/phinx)
- [Phinx CN](https://tsy12321.gitbooks.io/phinx-doc/content)
- [Phinx Old](http://docs.phinx.org/en/latest)
