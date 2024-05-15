<?php

declare (strict_types=1);
namespace _Z_PhinxVendor;

use function _Z_PhinxVendor\Cake\Core\deprecationWarning;
deprecationWarning('Since 4.1.0: Cake\\Database\\Schema\\PostgresSchema is deprecated. ' . 'Use Cake\\Database\\Schema\\PostgresSchemaDialect instead.');
\class_exists('_Z_PhinxVendor\\Cake\\Database\\Schema\\PostgresSchemaDialect');
