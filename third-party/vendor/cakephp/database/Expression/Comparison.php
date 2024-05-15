<?php

declare (strict_types=1);
namespace _Z_PhinxVendor;

use function _Z_PhinxVendor\Cake\Core\deprecationWarning;
deprecationWarning('Since 4.1.0: `Comparison` deprecated. Use `ComparisonExpression` instead.');
\class_exists('_Z_PhinxVendor\\Cake\\Database\\Expression\\ComparisonExpression');
