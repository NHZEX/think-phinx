<?php
declare(strict_types=1);

namespace HZEX\Phinx;

use Closure;
use HZEX\Phinx\Schema\Blueprint;
use HZEX\Phinx\Schema\TableDefinition;
use Phinx\Db\Table;
use Phinx\Migration\AbstractMigration;
use RuntimeException;

/**
 * Class Schema
 * @package HZEX\Phinx
 * @method void create(string $tableName, Closure $closure, AbstractMigration $migration = null) static
 * @method void update(string $tableName, Closure $closure, AbstractMigration $migration = null) static
 * @method void save(string $tableName, Closure $closure, AbstractMigration $migration = null) static
 */
class Schema
{
    /**
     * @var AbstractMigration
     */
    protected static $migration;

    protected $tableName;

    /**
     * @var TableDefinition
     */
    protected $tableOptions;

    /**
     * @var Blueprint
     */
    protected $blueprint;

    /**
     * @var Table
     */
    protected $table;

    protected function __construct(string $tableName, AbstractMigration $migration = null)
    {
        $this->tableName    = $tableName;
        $this->table        = ($migration ?? self::$migration)->table($this->tableName);
        $this->tableOptions = new TableDefinition();
        $this->blueprint    = new Blueprint($this);
    }

    public function getTable()
    {
        return $this->table;
    }

    public static function cxt(AbstractMigration $migration, Closure $closure)
    {
        $prev            = self::$migration;
        self::$migration = $migration;
        $closure();
        self::$migration = $prev;
    }

    public static function __callStatic($name, $arguments)
    {
        $method = ['create', 'update', 'save'];
        if (!in_array($name, $method)) {
            throw new RuntimeException('Call to undefined method ' . static::class . '::' . $name . '()');
        }

        /** @var string $tableName */
        /** @var Closure $closure */
        /** @var AbstractMigration $migration */
        [$tableName, $closure, $migration] = array_pad($arguments, 3, null);

        $schema = new static($tableName, $migration);

        $closure($schema->blueprint, $schema);

        $schema->table->getTable()->setOptions($schema->blueprint->mergeTableOptions());

        if (empty($schema->blueprint->getColumns()) && empty($schema->blueprint->getIndexs())) {
            // TODO 待完善
            return;
        }

        foreach ($schema->blueprint->getColumns() as $column) {
            if ($column->isChange()) {
                if ($name !== 'save') {
                    throw new RuntimeException('cannot change columns by create: ' . $column->getName());
                }
                $schema->table->changeColumn($column->getName(), $column->getColumn());
            } else {
                $schema->table->addColumn($column->getColumn());
            }
        }

        foreach ($schema->blueprint->getIndexs() as $index) {
            $schema->table->addIndex($index->getField(), $index->getOptions());
        }

        $schema->table->{$name}();
    }
}
