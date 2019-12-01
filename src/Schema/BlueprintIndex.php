<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/2/23
 * Time: 10:07
 */

namespace HZEX\Phinx\Schema;

class BlueprintIndex
{
    protected $options = [];

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
     * 获取列定义
     * @return array
     */
    public function d()
    {
        return $this->options;
    }
}
