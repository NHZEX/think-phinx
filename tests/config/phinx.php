<?php

return [
    'paths' => [
        'migrations' => [
            __DIR__ . '../_files/reversiblemigrations',
        ],
        'seeds'      => [
            __DIR__ . '../_files/empty_seed'
        ],
    ],
    'environments' => [
        'default_migration_table' => '_phinxlog',
    ],
    'aliases' => [
        'MakePermission' => '\Vendor\Package\Migration\Creation\MakePermission',
        'RemovePermission' => '\Vendor\Package\Migration\Creation\RemovePermission',
    ],
    'version_order' => 'creation'
];
