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
namespace _Z_PhinxVendor\Cake\Database;

use _Z_PhinxVendor\Cake\Database\Expression\FieldInterface;
use _Z_PhinxVendor\Cake\Database\Expression\IdentifierExpression;
use _Z_PhinxVendor\Cake\Database\Expression\OrderByExpression;
/**
 * Contains all the logic related to quoting identifiers in a Query object
 *
 * @internal
 */
class IdentifierQuoter
{
    /**
     * The driver instance used to do the identifier quoting
     *
     * @var \Cake\Database\Driver
     */
    protected $_driver;
    /**
     * Constructor
     *
     * @param \Cake\Database\Driver $driver The driver instance used to do the identifier quoting
     */
    public function __construct(Driver $driver)
    {
        $this->_driver = $driver;
    }
    /**
     * Iterates over each of the clauses in a query looking for identifiers and
     * quotes them
     *
     * @param \Cake\Database\Query $query The query to have its identifiers quoted
     * @return \Cake\Database\Query
     */
    public function quote(Query $query) : Query
    {
        $binder = $query->getValueBinder();
        $query->setValueBinder(null);
        if ($query->type() === 'insert') {
            $this->_quoteInsert($query);
        } elseif ($query->type() === 'update') {
            $this->_quoteUpdate($query);
        } else {
            $this->_quoteParts($query);
        }
        $query->traverseExpressions([$this, 'quoteExpression']);
        $query->setValueBinder($binder);
        return $query;
    }
    /**
     * Quotes identifiers inside expression objects
     *
     * @param \Cake\Database\ExpressionInterface $expression The expression object to walk and quote.
     * @return void
     */
    public function quoteExpression(ExpressionInterface $expression) : void
    {
        if ($expression instanceof FieldInterface) {
            $this->_quoteComparison($expression);
            return;
        }
        if ($expression instanceof OrderByExpression) {
            $this->_quoteOrderBy($expression);
            return;
        }
        if ($expression instanceof IdentifierExpression) {
            $this->_quoteIdentifierExpression($expression);
            return;
        }
    }
    /**
     * Quotes all identifiers in each of the clauses of a query
     *
     * @param \Cake\Database\Query $query The query to quote.
     * @return void
     */
    protected function _quoteParts(Query $query) : void
    {
        foreach (['distinct', 'select', 'from', 'group'] as $part) {
            $contents = $query->clause($part);
            if (!\is_array($contents)) {
                continue;
            }
            $result = $this->_basicQuoter($contents);
            if (!empty($result)) {
                $query->{$part}($result, \true);
            }
        }
        $joins = $query->clause('join');
        if ($joins) {
            $joins = $this->_quoteJoins($joins);
            $query->join($joins, [], \true);
        }
    }
    /**
     * A generic identifier quoting function used for various parts of the query
     *
     * @param array<string, mixed> $part the part of the query to quote
     * @return array<string, mixed>
     */
    protected function _basicQuoter(array $part) : array
    {
        $result = [];
        foreach ($part as $alias => $value) {
            $value = !\is_string($value) ? $value : $this->_driver->quoteIdentifier($value);
            $alias = \is_numeric($alias) ? $alias : $this->_driver->quoteIdentifier($alias);
            $result[$alias] = $value;
        }
        return $result;
    }
    /**
     * Quotes both the table and alias for an array of joins as stored in a Query
     * object
     *
     * @param array $joins The joins to quote.
     * @return array<string, array>
     */
    protected function _quoteJoins(array $joins) : array
    {
        $result = [];
        foreach ($joins as $value) {
            $alias = '';
            if (!empty($value['alias'])) {
                $alias = $this->_driver->quoteIdentifier($value['alias']);
                $value['alias'] = $alias;
            }
            if (\is_string($value['table'])) {
                $value['table'] = $this->_driver->quoteIdentifier($value['table']);
            }
            $result[$alias] = $value;
        }
        return $result;
    }
    /**
     * Quotes the table name and columns for an insert query
     *
     * @param \Cake\Database\Query $query The insert query to quote.
     * @return void
     */
    protected function _quoteInsert(Query $query) : void
    {
        $insert = $query->clause('insert');
        if (!isset($insert[0]) || !isset($insert[1])) {
            return;
        }
        [$table, $columns] = $insert;
        $table = $this->_driver->quoteIdentifier($table);
        foreach ($columns as &$column) {
            if (\is_scalar($column)) {
                $column = $this->_driver->quoteIdentifier((string) $column);
            }
        }
        $query->insert($columns)->into($table);
    }
    /**
     * Quotes the table name for an update query
     *
     * @param \Cake\Database\Query $query The update query to quote.
     * @return void
     */
    protected function _quoteUpdate(Query $query) : void
    {
        $table = $query->clause('update')[0];
        if (\is_string($table)) {
            $query->update($this->_driver->quoteIdentifier($table));
        }
    }
    /**
     * Quotes identifiers in expression objects implementing the field interface
     *
     * @param \Cake\Database\Expression\FieldInterface $expression The expression to quote.
     * @return void
     */
    protected function _quoteComparison(FieldInterface $expression) : void
    {
        $field = $expression->getField();
        if (\is_string($field)) {
            $expression->setField($this->_driver->quoteIdentifier($field));
        } elseif (\is_array($field)) {
            $quoted = [];
            foreach ($field as $f) {
                $quoted[] = $this->_driver->quoteIdentifier($f);
            }
            $expression->setField($quoted);
        } elseif ($field instanceof ExpressionInterface) {
            $this->quoteExpression($field);
        }
    }
    /**
     * Quotes identifiers in "order by" expression objects
     *
     * Strings with spaces are treated as literal expressions
     * and will not have identifiers quoted.
     *
     * @param \Cake\Database\Expression\OrderByExpression $expression The expression to quote.
     * @return void
     */
    protected function _quoteOrderBy(OrderByExpression $expression) : void
    {
        $expression->iterateParts(function ($part, &$field) {
            if (\is_string($field)) {
                $field = $this->_driver->quoteIdentifier($field);
                return $part;
            }
            if (\is_string($part) && \strpos($part, ' ') === \false) {
                return $this->_driver->quoteIdentifier($part);
            }
            return $part;
        });
    }
    /**
     * Quotes identifiers in "order by" expression objects
     *
     * @param \Cake\Database\Expression\IdentifierExpression $expression The identifiers to quote.
     * @return void
     */
    protected function _quoteIdentifierExpression(IdentifierExpression $expression) : void
    {
        $expression->setIdentifier($this->_driver->quoteIdentifier($expression->getIdentifier()));
    }
}
