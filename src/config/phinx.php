<?php

return [
    'adapter_mapping' => [],
    'paths' => [
        'migrations' => [
            'DbMigrations' => './phinx/migrations',
        ],
        'seeds' => [
            'DbSeeds' => './phinx/seeds'
        ]
    ],
    'environments' => [
        'default_migration_table' => '_phinxlog',
    ],
    'version_order' => 'creation'
];
