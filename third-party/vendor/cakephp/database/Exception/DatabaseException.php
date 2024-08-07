<?php

declare (strict_types=1);
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         3.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace _Z_PhinxVendor\Cake\Database\Exception;

use _Z_PhinxVendor\Cake\Core\Exception\CakeException;
/**
 * Exception for the database package.
 */
class DatabaseException extends CakeException
{
    /**
     * @inheritDoc
     */
    protected $_messageTemplate = '%s';
}
// phpcs:disable
\class_alias('_Z_PhinxVendor\\Cake\\Database\\Exception\\DatabaseException', '_Z_PhinxVendor\\Cake\\Database\\Exception');
// phpcs:enable
