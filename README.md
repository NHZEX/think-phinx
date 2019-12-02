# Think-Phinx
thinkphp 6.0 phinx 数据迁移  
phinx: ~0.11.1  

[![Latest Stable Version](https://poser.pugx.org/nhzex/think-phinx/v/stable)](https://packagist.org/packages/nhzex/think-blade)
[![License](https://poser.pugx.org/nhzex/think-phinx/license)](https://packagist.org/packages/nhzex/think-blade)
[![workflows](https://github.com/nhzex/think-phinx/workflows/ci/badge.svg)](https://github.com/NHZEX/think-blade/actions)
[![coverage](https://codecov.io/gh/nhzex/think-phinx/graph/badge.svg)](https://codecov.io/gh/nhzex/think-phinx)

## Installation
composer require nhzex/think-phinx

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
```php
<?php
return [
    'paths' => [
        'migrations' => [
            'DbMigrations' => './.phinx/migrations',
        ],
        'seeds' => [
            'DbSeeds' => './.phinx/seeds'
        ]
    ],
    'environments' => [
        'default_migration_table' => '_phinxlog',
    ],
    'version_order' => 'creation'
];

```

## Doc
- [Phinx CN](https://tsy12321.gitbooks.io/phinx-doc/content)
- [Phinx EN](http://docs.phinx.org/en/latest)
- [Phinx CakePHP](https://book.cakephp.org/3/en/phinx.html)
