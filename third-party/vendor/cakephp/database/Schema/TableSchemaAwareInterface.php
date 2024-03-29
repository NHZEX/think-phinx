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
 * @since         3.5.0
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace _Z_PhinxVendor\Cake\Database\Schema;

/**
 * Defines the interface for getting the schema.
 */
interface TableSchemaAwareInterface
{
    /**
     * Get and set the schema for this fixture.
     *
     * @return \Cake\Database\Schema\TableSchemaInterface&\Cake\Database\Schema\SqlGeneratorInterface
     */
    public function getTableSchema();
    /**
     * Get and set the schema for this fixture.
     *
     * @param \Cake\Database\Schema\TableSchemaInterface&\Cake\Database\Schema\SqlGeneratorInterface $schema The table to set.
     * @return $this
     */
    public function setTableSchema($schema);
}
