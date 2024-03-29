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
namespace _Z_PhinxVendor\Cake\Database\Expression;

use _Z_PhinxVendor\Cake\Database\ExpressionInterface;
use _Z_PhinxVendor\Cake\Database\Type\ExpressionTypeCasterTrait;
use _Z_PhinxVendor\Cake\Database\ValueBinder;
use Closure;
/**
 * This class represents a SQL Case statement
 *
 * @deprecated 4.3.0 Use QueryExpression::case() or CaseStatementExpression instead
 */
class CaseExpression implements ExpressionInterface
{
    use ExpressionTypeCasterTrait;
    /**
     * A list of strings or other expression objects that represent the conditions of
     * the case statement. For example one key of the array might look like "sum > :value"
     *
     * @var array
     */
    protected $_conditions = [];
    /**
     * Values that are associated with the conditions in the $_conditions array.
     * Each value represents the 'true' value for the condition with the corresponding key.
     *
     * @var array
     */
    protected $_values = [];
    /**
     * The `ELSE` value for the case statement. If null then no `ELSE` will be included.
     *
     * @var \Cake\Database\ExpressionInterface|array|string|null
     */
    protected $_elseValue;
    /**
     * Constructs the case expression
     *
     * @param \Cake\Database\ExpressionInterface|array $conditions The conditions to test. Must be a ExpressionInterface
     * instance, or an array of ExpressionInterface instances.
     * @param \Cake\Database\ExpressionInterface|array $values Associative array of values to be associated with the
     * conditions passed in $conditions. If there are more $values than $conditions,
     * the last $value is used as the `ELSE` value.
     * @param array<string> $types Associative array of types to be associated with the values
     * passed in $values
     */
    public function __construct($conditions = [], $values = [], $types = [])
    {
        $conditions = \is_array($conditions) ? $conditions : [$conditions];
        $values = \is_array($values) ? $values : [$values];
        $types = \is_array($types) ? $types : [$types];
        if (!empty($conditions)) {
            $this->add($conditions, $values, $types);
        }
        if (\count($values) > \count($conditions)) {
            \end($values);
            $key = \key($values);
            $this->elseValue($values[$key], $types[$key] ?? null);
        }
    }
    /**
     * Adds one or more conditions and their respective true values to the case object.
     * Conditions must be a one dimensional array or a QueryExpression.
     * The trueValues must be a similar structure, but may contain a string value.
     *
     * @param \Cake\Database\ExpressionInterface|array $conditions Must be a ExpressionInterface instance,
     *   or an array of ExpressionInterface instances.
     * @param \Cake\Database\ExpressionInterface|array $values Associative array of values of each condition
     * @param array<string> $types Associative array of types to be associated with the values
     * @return $this
     */
    public function add($conditions = [], $values = [], $types = [])
    {
        $conditions = \is_array($conditions) ? $conditions : [$conditions];
        $values = \is_array($values) ? $values : [$values];
        $types = \is_array($types) ? $types : [$types];
        $this->_addExpressions($conditions, $values, $types);
        return $this;
    }
    /**
     * Iterates over the passed in conditions and ensures that there is a matching true value for each.
     * If no matching true value, then it is defaulted to '1'.
     *
     * @param array $conditions Array of ExpressionInterface instances.
     * @param array<mixed> $values Associative array of values of each condition
     * @param array<string> $types Associative array of types to be associated with the values
     * @return void
     */
    protected function _addExpressions(array $conditions, array $values, array $types) : void
    {
        $rawValues = \array_values($values);
        $keyValues = \array_keys($values);
        foreach ($conditions as $k => $c) {
            $numericKey = \is_numeric($k);
            if ($numericKey && empty($c)) {
                continue;
            }
            if (!$c instanceof ExpressionInterface) {
                continue;
            }
            $this->_conditions[] = $c;
            $value = $rawValues[$k] ?? 1;
            if ($value === 'literal') {
                $value = $keyValues[$k];
                $this->_values[] = $value;
                continue;
            }
            if ($value === 'identifier') {
                /** @var string $identifier */
                $identifier = $keyValues[$k];
                $value = new IdentifierExpression($identifier);
                $this->_values[] = $value;
                continue;
            }
            $type = $types[$k] ?? null;
            if ($type !== null && !$value instanceof ExpressionInterface) {
                $value = $this->_castToExpression($value, $type);
            }
            if ($value instanceof ExpressionInterface) {
                $this->_values[] = $value;
                continue;
            }
            $this->_values[] = ['value' => $value, 'type' => $type];
        }
    }
    /**
     * Sets the default value
     *
     * @param \Cake\Database\ExpressionInterface|array|string|null $value Value to set
     * @param string|null $type Type of value
     * @return void
     */
    public function elseValue($value = null, ?string $type = null) : void
    {
        if (\is_array($value)) {
            \end($value);
            $value = \key($value);
        }
        if ($value !== null && !$value instanceof ExpressionInterface) {
            $value = $this->_castToExpression($value, $type);
        }
        if (!$value instanceof ExpressionInterface) {
            $value = ['value' => $value, 'type' => $type];
        }
        $this->_elseValue = $value;
    }
    /**
     * Compiles the relevant parts into sql
     *
     * @param \Cake\Database\ExpressionInterface|array|string $part The part to compile
     * @param \Cake\Database\ValueBinder $binder Sql generator
     * @return string
     */
    protected function _compile($part, ValueBinder $binder) : string
    {
        if ($part instanceof ExpressionInterface) {
            $part = $part->sql($binder);
        } elseif (\is_array($part)) {
            $placeholder = $binder->placeholder('param');
            $binder->bind($placeholder, $part['value'], $part['type']);
            $part = $placeholder;
        }
        return $part;
    }
    /**
     * Converts the Node into a SQL string fragment.
     *
     * @param \Cake\Database\ValueBinder $binder Placeholder generator object
     * @return string
     */
    public function sql(ValueBinder $binder) : string
    {
        $parts = [];
        $parts[] = 'CASE';
        foreach ($this->_conditions as $k => $part) {
            $value = $this->_values[$k];
            $parts[] = 'WHEN ' . $this->_compile($part, $binder) . ' THEN ' . $this->_compile($value, $binder);
        }
        if ($this->_elseValue !== null) {
            $parts[] = 'ELSE';
            $parts[] = $this->_compile($this->_elseValue, $binder);
        }
        $parts[] = 'END';
        return \implode(' ', $parts);
    }
    /**
     * @inheritDoc
     */
    public function traverse(Closure $callback)
    {
        foreach (['_conditions', '_values'] as $part) {
            foreach ($this->{$part} as $c) {
                if ($c instanceof ExpressionInterface) {
                    $callback($c);
                    $c->traverse($callback);
                }
            }
        }
        if ($this->_elseValue instanceof ExpressionInterface) {
            $callback($this->_elseValue);
            $this->_elseValue->traverse($callback);
        }
        return $this;
    }
}
