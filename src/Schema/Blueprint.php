<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/1/28
 * Time: 17:46
 */

namespace HZEX\Phinx\Schema;

use Phinx\Db\Adapter\MysqlAdapter as M;
use Phinx\Db\Table\Column;
use think\helper\Str;

/**
 * 字段构造增强
 * Class Blueprint
 * @method Blueprint integer(string $name) static 相当于 INTEGER
 * @method Blueprint unsignedInteger(string $name) static 相当于 Unsigned INTEGER
 * @method Blueprint tinyInteger(string $name) static 相当于 TINYINT
 * @method Blueprint unsignedTinyInteger(string $name) static 相当于 Unsigned TINYINT
 * @method Blueprint string(string $name, int $limit) static 相当于带长度的 VARCHAR
 * @method Blueprint char(string $name, int $limit) static 相当于带有长度的 CHAR
 * @method Blueprint json(string $name) static 相当于 JSON
 * @method Blueprint text(string $name) static 相当于 TEXT
 * @method Blueprint smallInteger(string $name) static 相当于 SMALLINT
 * @method Blueprint unsignedSmallInteger(string $name) static 相当于 Unsigned SMALLINT
 *
 * @method Blueprint lockVersion() static lockVersion
 * @method Blueprint createTime() static createTime
 * @method Blueprint updateTime() static updateTime
 * @method Blueprint deleteTime() static deleteTime
 * @method Blueprint createBy() static createBy
 * @method Blueprint updateBy() static updateBy
 * @method Blueprint uuid() static uuid
 * @method Blueprint status() static status
 * @method Blueprint genre() static genre
 * @method Blueprint remark() static remark
 */
class Blueprint
{
    /** @var Column */
    protected $column;

    const COMMENTS = [
        'createTime' => '创建时间',
        'updateTime' => '更新时间',
        'deleteTime' => '删除时间',
        'createBy' => '创建者',
        'updateBy' => '更新者',
        'creatorUid' => '创建者',
        'editorUid' => '编辑者',
        'status' => '状态',
        'genre' => '类型',
        'remark' => '备注',
    ];

    const TYPE_MAPPING = [
        'integer' => [M::PHINX_TYPE_INTEGER, M::INT_REGULAR],
        'smallInteger' => [M::PHINX_TYPE_INTEGER, M::INT_SMALL],
        'tinyInteger' => [M::PHINX_TYPE_INTEGER, M::INT_TINY],

        'unsignedInteger' => [M::PHINX_TYPE_INTEGER, M::INT_REGULAR],
        'unsignedSmallInteger' => [M::PHINX_TYPE_INTEGER, M::INT_SMALL],
        'unsignedTinyInteger' => [M::PHINX_TYPE_INTEGER, M::INT_TINY],

        'string' => [M::PHINX_TYPE_STRING, M::TEXT_TINY],
        'char' => [M::PHINX_TYPE_CHAR, null],
        'text' => [M::PHINX_TYPE_TEXT, null],
        'json' => [M::PHINX_TYPE_JSON, null],

        'lockVersion' => [M::PHINX_TYPE_INTEGER, M::INT_REGULAR],
        'createTime' => [M::PHINX_TYPE_INTEGER, M::INT_REGULAR],
        'updateTime' => [M::PHINX_TYPE_INTEGER, M::INT_REGULAR],
        'deleteTime' => [M::PHINX_TYPE_INTEGER, M::INT_REGULAR],
        'createBy' => [M::PHINX_TYPE_INTEGER, M::INT_REGULAR],
        'updateBy' => [M::PHINX_TYPE_INTEGER, M::INT_REGULAR],
        'creatorUid' => [M::PHINX_TYPE_INTEGER, M::INT_REGULAR],
        'editorUid' => [M::PHINX_TYPE_INTEGER, M::INT_REGULAR],
        'uuid' => [M::PHINX_TYPE_STRING, 36],
        'status' => [M::PHINX_TYPE_INTEGER, M::INT_TINY],
        'genre' => [M::PHINX_TYPE_INTEGER, M::INT_TINY],
        'remark' => [M::PHINX_TYPE_STRING, 255],

    ];

    protected function __construct(Column $column)
    {
        $this->column = $column;
    }

    public static function __callStatic($callName, $arguments)
    {
        [$name, $input1] = array_pad($arguments, 2, null);

        null === $name && $name = Str::snake($callName);
        [$type, $limit] = self::TYPE_MAPPING[$callName];

        $column = new Column();
        $column->setName($name);
        $column->setType($type);
        // 全局设置
        $limit && $column->setLimit($limit);
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
                $column->setLimit($input1);
                $column->setDefault('');
                break;
            case 'char':
                $column->setLimit($input1);
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

        return new static($column);
    }

    public static function table()
    {
        return new BlueprintTable();
    }

    public static function index()
    {
        return new BlueprintIndex();
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
     * 获取列定义
     * @return Column
     */
    public function d()
    {
        return $this->column;
    }
}
