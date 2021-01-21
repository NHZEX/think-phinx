<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/2/23
 * Time: 10:07
 */

namespace HZEX\Phinx\Schema;

use ValueError;
use function strtoupper;

class IndexDefinition
{
    protected $options = [];

    /**
     * @param string|string[] $field
     * @return string
     */
    public static function generateName($field)
    {
        if (is_array($field)) {
            $name = implode('_', $field);
        } else {
            $name = $field;
        }
        return $name;
    }

    /**
     * @var string|string[]|null
     */
    protected $field = null;

    public function __construct($field = null)
    {
        $this->field = $field;
    }

    /**
     * 是否唯一
     * @param bool $value
     * @return $this
     */
    public function unique(bool $value = true)
    {
        $this->options['unique'] = $value;
        return $this;
    }

    /**
     * 索引长度
     * @param int|array $value
     * @return $this
     */
    public function limit($value)
    {
        $this->options['limit'] = $value;
        return $this;
    }

    /**
     * 索引名称
     * @param string $name
     * @return $this
     */
    public function name(string $name)
    {
        $this->options['name'] = $name;
        return $this;
    }

    /**
     * 设置主键字段为 UNSIGNED
     * @param bool $enable
     * @return $this
     */
    public function unsigned(bool $enable = true)
    {
        $this->options['signed'] = !$enable;
        return $this;
    }

    /**
     * 定义表注释
     * @param $text
     * @return $this
     */
    public function comment(string $text)
    {
        $this->options['comment'] = $text;
        return $this;
    }

    /**
     * 设置 fulltext 索引 (mysql)
     * @param bool $enable
     * @return $this
     */
    public function fulltext(bool $enable = true)
    {
        if (!$enable && isset($this->options['type']) && 'fulltext' === $this->options['type']) {
            unset($this->options['type']);
        } else {
            $this->options['type'] = 'fulltext';
        }
        return $this;
    }

    /**
     * 定义表排序规则
     * @param $value
     * @return $this
     */
    public function collation(string $value)
    {
        $this->options['collation'] = $value;
        return $this;
    }

    /**
     * @param array $order
     * @return $this
     */
    public function order(array $order)
    {
        foreach ($order as $field => $value) {
            $value = strtoupper($value);
            if ($value !== 'DESC' && $value !== 'ASC') {
                throw new ValueError('order value can only be DESC or ASC');
            }
        }
        $this->options['order'] = $order;
        return $this;
    }

    public function getField()
    {
        return $this->field;
    }

    public function getOptions()
    {
        return $this->options;
    }
}
