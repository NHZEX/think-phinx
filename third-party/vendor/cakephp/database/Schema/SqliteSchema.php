<?php

declare (strict_types=1);
namespace _Z_PhinxVendor;

use function _Z_PhinxVendor\Cake\Core\deprecationWarning;
deprecationWarning('Since 4.1.0: Cake\\Database\\Schema\\SqliteSchema is deprecated. ' . 'Use Cake\\Database\\Schema\\SqliteSchemaDialect instead.');
\class_exists('_Z_PhinxVendor\\Cake\\Database\\Schema\\SqliteSchemaDialect');
