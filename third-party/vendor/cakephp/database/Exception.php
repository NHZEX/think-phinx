<?php

declare (strict_types=1);
namespace _Z_PhinxVendor;

use function _Z_PhinxVendor\Cake\Core\deprecationWarning;
deprecationWarning('Since 4.2.0: Cake\\Database\\Exception is deprecated. ' . 'Use Cake\\Database\\Exception\\DatabaseException instead.');
\class_exists('_Z_PhinxVendor\\Cake\\Database\\Exception\\DatabaseException');
