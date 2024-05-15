<?php

declare (strict_types=1);
namespace _Z_PhinxVendor;

use function _Z_PhinxVendor\Cake\Core\deprecationWarning;
deprecationWarning('Since 4.1.0: Cake\\Database\\Schema\\SqlserverSchema is deprecated. ' . 'Use Cake\\Database\\Schema\\SqlServerSchemaDialect instead.');
\class_exists('_Z_PhinxVendor\\Cake\\Database\\Schema\\SqlServerSchemaDialect');
