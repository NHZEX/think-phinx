<?php
declare(strict_types=1);

namespace HZEX\Phinx\Schema;

use HZEX\Phinx\Schema;
use Phinx\Db\Table;
use RuntimeException;

/**
 * Class Schema
 * @package HZEX\Phinx
 *
 * @property-write string|bool  $id         自定义主键名称 / false=关闭主键 / true=生成主键
 * @property-write string|array $primaryKey 自定义主键字段
 * @property-write bool         $unsigned   设置主键字段为 UNSIGNED
 * @property-write string       $comment    定义表注释
 * @property-write string       $collation  定义表排序规则
 *
 * @method ColumnDefinition column(string $type, string $name)
 *
 * @method ColumnDefinition integer(string $name) 相当于 INTEGER
 * @method ColumnDefinition unsignedInteger(string $name) 相当于 Unsigned INTEGER
 * @method ColumnDefinition smallInteger(string $name) 相当于 SMALLINT
 * @method ColumnDefinition unsignedSmallInteger(string $name) 相当于 Unsigned SMALLINT
 * @method ColumnDefinition tinyInteger(string $name) 相当于 TINYINT
 * @method ColumnDefinition unsignedTinyInteger(string $name) 相当于 Unsigned TINYINT
 * @method ColumnDefinition string(string $name, int $limit) 相当于带长度的 VARCHAR
 * @method ColumnDefinition char(string $name, int $limit) 相当于带有长度的 CHAR
 * @method ColumnDefinition text(string $name) 相当于 TEXT
 * @method ColumnDefinition blob(string $name) 相当于 BLOB
 * @method ColumnDefinition json(string $name) 相当于 JSON
 *
 * @method ColumnDefinition lockVersion() lock_version
 * @method ColumnDefinition createTime() create_time
 * @method ColumnDefinition updateTime() update_time
 * @method ColumnDefinition deleteTime() delete_time
 * @method ColumnDefinition createBy() create_by
 * @method ColumnDefinition updateBy() update_by
 * @method ColumnDefinition uuid() uuid
 * @method ColumnDefinition status() status
 * @method ColumnDefinition genre() genre
 * @method ColumnDefinition remark() remark
 */
class Blueprint
{
    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @var TableDefinition
     */
    protected $tableDefinition;

    /**
     * @var ColumnDefinition[]
     */
    protected $columns = [];

    /**
     * @var IndexDefinition[]
     */
    protected $indexs = [];
    /**
     * @var Table
     */
    public $table;

    public function __construct(Schema $schema)
    {
        $this->schema          = $schema;
        $this->table           = $schema->getTable();
        $this->tableDefinition = new TableDefinition();
    }

    /**
     * 获取表单定义对象
     * @return TableDefinition
     */
    public function getOptions()
    {
        return $this->tableDefinition;
    }

    /**
     * 获取合并表定义选项
     * @return array
     */
    public function mergeTableOptions()
    {
        return array_merge($this->schema->getTable()->getOptions(), $this->tableDefinition->getOptions());
    }

    /**
     * 设置表单定义
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->tableDefinition->{$name}($value);
    }

    /**
     * @param string $callName
     * @param array  $arguments
     * @return ColumnDefinition
     */
    public function __call(string $callName, array $arguments)
    {
        $column          = ColumnDefinition::make($callName, $arguments);
        if (isset($this->columns[$column->getName()])) {
            throw new RuntimeException('duplicate definition column ' . $column->getName());
        }
        $this->columns[$column->getName()] = $column;
        return $column;
    }

    /**
     * 添加普通索引
     * @param string|string[] $field
     * @param string|null     $name
     * @return IndexDefinition
     */
    public function index($field, string $name = null)
    {
        $index = new IndexDefinition($field);
        $name = $name ?? IndexDefinition::generateName($field);
        $index->name($name);
        if (isset($this->indexs[$name])) {
            throw new RuntimeException('duplicate definition index ' . $name);
        }
        $this->indexs[$name] = $index;
        return $index;
    }

    /**
     * 添加唯一索引
     * @param string|string[] $field
     * @return IndexDefinition
     */
    public function unique($field)
    {
        $index = $this->index($field);
        $index->unique();
        return $index;
    }

    /**
     * @param ColumnDefinition $column
     * @return ColumnDefinition
     */
    public function addColumn(ColumnDefinition $column)
    {
        if (empty($column->getName())) {
            throw new RuntimeException('column name is empty');
        }
        if (isset($this->columns[$column->getName()])) {
            throw new RuntimeException('duplicate definition column ' . $column->getName());
        }
        $this->columns[$column->getName()] = $column;
        return $column;
    }

    /**
     * @return ColumnDefinition[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return IndexDefinition[]
     */
    public function getIndexs(): array
    {
        return $this->indexs;
    }
}
