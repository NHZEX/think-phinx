<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/1/28
 * Time: 17:46
 */

namespace HZEX\Phinx\Schema;

use Phinx\Db\Adapter\AdapterInterface as Adapter;
use Phinx\Db\Table\Column;
use think\helper\Str;

/**
 * 字段构造增强
 * Class Blueprint
 * @method ColumnDefinition integer(string $name) static 相当于 INTEGER
 * @method ColumnDefinition unsignedInteger(string $name) static 相当于 Unsigned INTEGER
 * @method ColumnDefinition tinyInteger(string $name) static 相当于 TINYINT
 * @method ColumnDefinition unsignedTinyInteger(string $name) static 相当于 Unsigned TINYINT
 * @method ColumnDefinition string(string $name, int $limit) static 相当于带长度的 VARCHAR
 * @method ColumnDefinition char(string $name, int $limit) static 相当于带有长度的 CHAR
 * @method ColumnDefinition json(string $name) static 相当于 JSON
 * @method ColumnDefinition text(string $name) static 相当于 TEXT
 * @method ColumnDefinition smallInteger(string $name) static 相当于 SMALLINT
 * @method ColumnDefinition unsignedSmallInteger(string $name) static 相当于 Unsigned SMALLINT
 *
 * @method ColumnDefinition lockVersion() static lockVersion
 * @method ColumnDefinition createTime() static createTime
 * @method ColumnDefinition updateTime() static updateTime
 * @method ColumnDefinition deleteTime() static deleteTime
 * @method ColumnDefinition createBy() static createBy
 * @method ColumnDefinition updateBy() static updateBy
 * @method ColumnDefinition uuid() static uuid
 * @method ColumnDefinition status() static status
 * @method ColumnDefinition genre() static genre
 * @method ColumnDefinition remark() static remark
 */
class ColumnDefinition
{
    const COMMENTS = [
        'createTime' => '创建时间',
        'updateTime' => '更新时间',
        'deleteTime' => '删除时间',
        'createBy'   => '创建者',
        'updateBy'   => '更新者',
        'creatorUid' => '创建者',
        'editorUid'  => '编辑者',
        'status'     => '状态',
        'genre'      => '类型',
        'remark'     => '备注',
    ];

    const MAPPING = [
        'integer'      => [Adapter::PHINX_TYPE_INTEGER, 4294967295], // INT_REGULAR
        'smallInteger' => [Adapter::PHINX_TYPE_INTEGER, 65535], // INT_SMALL
        'tinyInteger'  => [Adapter::PHINX_TYPE_INTEGER, 255], // INT_TINY

        'unsignedInteger'      => [Adapter::PHINX_TYPE_INTEGER, 4294967295], // INT_REGULAR
        'unsignedSmallInteger' => [Adapter::PHINX_TYPE_INTEGER, 65535], // INT_SMALL
        'unsignedTinyInteger'  => [Adapter::PHINX_TYPE_INTEGER, 255], // INT_TINY

        'string' => [Adapter::PHINX_TYPE_STRING, 255], // TEXT_TINY
        'char'   => [Adapter::PHINX_TYPE_CHAR, null],
        'text'   => [Adapter::PHINX_TYPE_TEXT, null],
        'json'   => [Adapter::PHINX_TYPE_JSON, null],

        'lockVersion' => [Adapter::PHINX_TYPE_INTEGER, 4294967295],
        'createTime'  => [Adapter::PHINX_TYPE_INTEGER, 4294967295],
        'updateTime'  => [Adapter::PHINX_TYPE_INTEGER, 4294967295],
        'deleteTime'  => [Adapter::PHINX_TYPE_INTEGER, 4294967295],
        'createBy'    => [Adapter::PHINX_TYPE_INTEGER, 4294967295],
        'updateBy'    => [Adapter::PHINX_TYPE_INTEGER, 4294967295],
        'creatorUid'  => [Adapter::PHINX_TYPE_INTEGER, 4294967295],
        'editorUid'   => [Adapter::PHINX_TYPE_INTEGER, 4294967295],
        'status'      => [Adapter::PHINX_TYPE_INTEGER, 255],
        'genre'       => [Adapter::PHINX_TYPE_INTEGER, 255],
        'uuid'        => [Adapter::PHINX_TYPE_STRING, 36],
        'remark'      => [Adapter::PHINX_TYPE_STRING, 255],
    ];

    protected $change = false;

    /** @var Column */
    protected $column;

    protected function __construct(Column $column)
    {
        $this->column = $column;
    }

    public static function __callStatic($name, $arguments)
    {
        return self::make($name, $arguments);
    }

    public static function make($callName, $arguments)
    {
        [$name, $arg1] = array_pad($arguments, 2, null);

        /**
         * $name == null : field
         * $name <> null : type
         */
        $name = $name ?? Str::snake($callName);

        [$type, $limit] = self::MAPPING[$callName];

        $column = new Column();
        $column->setName($name);
        $column->setType($type);

        // 默认设置
        if (null !== $limit) {
            $column->setLimit($limit);
        }
        $column->setNull(false);

        // 按需设置
        switch ($callName) {
            case 'integer':
            case 'smallInteger':
            case 'tinyInteger':
                $column->setDefault(0);
                break;
            case 'unsignedInteger':
            case 'unsignedSmallInteger':
            case 'unsignedTinyInteger':
            case 'status':
            case 'genre':
                $column->setSigned(false);
                $column->setDefault(0);
                break;
            case 'string':
                $column->setLimit($arg1);
                $column->setDefault('');
                break;
            case 'char':
                $column->setLimit($arg1);
                break;
            case 'lockVersion':
            case 'createTime':
            case 'updateTime':
            case 'deleteTime':
            case 'creatorUid':
            case 'editorUid':
            case 'createBy':
            case 'updateBy':
                $column->setSigned(false);
                $column->setDefault(0);
                $column->setComment(self::COMMENTS[$callName] ?? null);
                break;
            case 'uuid':
                $column->setCollation('ascii');
                $column->setCollation('ascii_general_ci');
                break;
            case 'remark':
                $column->setDefault('');
                break;
        }

        return new ColumnDefinition($column);
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->column->getName();
    }

    /**
     * 将此字段放置在其它字段 "之后" (MySQL)
     * @param string $columnName
     * @return $this
     */
    public function after(string $columnName)
    {
        $this->column->setAfter($columnName);
        return $this;
    }

    /**
     * 将 INTEGER 类型的字段设置为自动递增的主键
     * @param bool $enable
     * @return $this
     */
    public function autoIncrement(bool $enable = true)
    {
        $this->column->setIdentity($enable);
        return $this;
    }

    /**
     * 指定一个字符集 (MySQL)
     * @param string $collation eg: utf8mb4
     * @return $this
     */
    public function charset(string $collation)
    {
        $this->column->setCollation($collation);
        return $this;
    }

    /**
     * 指定列的排序规则 (MySQL)
     * @param string $encoding  eg: utf8mb4_general_ci
     * @return $this
     */
    public function collation(string $encoding)
    {
        $this->column->setCollation($encoding);
        return $this;
    }

    public function ccAscii()
    {
        $this->column->setCollation('ascii');
        $this->column->setCollation('ascii_general_ci');
        return $this;
    }

    /**
     * 为字段增加注释
     * @param string $comment
     * @return $this
     */
    public function comment(string $comment)
    {
        $this->column->setComment($comment);
        return $this;
    }

    /**
     * 为字段指定 "默认" 值
     * @param string|null $default
     * @return $this
     */
    public function default(?string $default)
    {
        $this->column->setDefault($default);
        return $this;
    }

    /**
     * 此字段允许写入 NULL 值
     * @param bool $enable
     * @return $this
     */
    public function nullable(bool $enable = false)
    {
        $this->column->setNull($enable);
        return $this;
    }

    /**
     * 设置 INTEGER 类型的字段为 UNSIGNED (MySQL)
     * @param bool $enable
     * @return $this
     */
    public function unsigned(bool $enable = true)
    {
        $this->column->setSigned(!$enable);
        return $this;
    }

    public function limit(int $limit)
    {
        $this->column->setLimit($limit);
        return $this;
    }

    public function identity(bool $enable)
    {
        $this->column->setIdentity($enable);
        return $this;
    }

    /**
     * @return $this
     */
    public function change()
    {
        $this->change = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function isChange(): bool
    {
        return $this->change;
    }

    /**
     * 获取列
     * @return Column
     */
    public function getColumn()
    {
        return $this->column;
    }
}
