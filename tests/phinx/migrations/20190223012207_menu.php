<?php
namespace DbMigrations;

use HZEX\Phinx\Schema;
use Phinx\Migration\AbstractMigration;

class Menu extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        Schema::cxt($this, function () {
            Schema::create('system_menu', function (Schema\Blueprint $blueprint) {
                $blueprint->comment = '系统菜单';
                $blueprint->unsigned = true;

                $blueprint->unsignedInteger('pid')->comment('父关联');
                $blueprint->smallInteger('sort')->comment('菜单排序');
                $blueprint->status()->comment('菜单状态');
                $blueprint->string('node', 8)->ccAscii()->comment('关联节点');
                $blueprint->string('title', 64)->comment('菜单标题');
                $blueprint->string('icon', 64)->comment('菜单图标');
                $blueprint->string('url', 256)->comment('菜单地址');
                $blueprint->lockVersion();
                $blueprint->createTime();
                $blueprint->updateTime();
                $blueprint->deleteTime();
            });
        });
    }
}
