<?php

declare (strict_types=1);
namespace _Z_PhinxVendor;

use function _Z_PhinxVendor\Cake\Core\deprecationWarning;
deprecationWarning('Since 4.2.0: Cake\\Datasource\\SimplePaginator is deprecated. ' . 'Use Cake\\Datasource\\Paging\\SimplePaginator instead.');
\class_exists('_Z_PhinxVendor\\Cake\\Datasource\\Paging\\SimplePaginator');
