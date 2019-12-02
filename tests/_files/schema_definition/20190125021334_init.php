<?php
namespace DbMigrations;

use HZEX\Phinx\Schema;
use Phinx\Migration\AbstractMigration;

class Init extends AbstractMigration
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
     * rollback the migration.Migration has pending actions after execution!
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        Schema::cxt($this, function () {
            Schema::create('system', function (Schema\Blueprint $blueprint) {
                $blueprint->id = false;
                $blueprint->primaryKey = 'label';
                $blueprint->comment = '标签';

                $blueprint->string('label', 48)->ccAscii()->comment('标签');
                $blueprint->string('value', 255)->comment('值');
            });

            Schema::create('role', function (Schema\Blueprint $blueprint) {
                $blueprint->comment = '角色';
                $blueprint->unsigned = true;

                $blueprint->genre()->comment('角色类型');
                $blueprint->status()->comment('角色状态');
                $blueprint->createTime();
                $blueprint->updateTime();
                $blueprint->deleteTime();
                $blueprint->string('name', 32)->comment('角色名称');
                $blueprint->string('description', 128)->comment('角色描述');
                $blueprint->json('ext')->comment('角色权限');
                $blueprint->lockVersion();
            });

            Schema::create('permission', function (Schema\Blueprint $blueprint) {
                $blueprint->comment = '权限';
                $blueprint->unsigned = true;

                $blueprint->unsignedInteger('pid')->comment('父节点ID');
                $blueprint->genre()->comment('节点类型');
                $blueprint->string('nkey', 128)->ccAscii()->comment('节点命名key');
                $blueprint->string('hash', 8)->ccAscii()->comment('节点命名hash');
                $blueprint->string('lkey', 64)->ccAscii()->comment('节点逻辑key');
                $blueprint->unsignedTinyInteger('level')->comment('节点层级');
                $blueprint->string('action', 32)->ccAscii()->comment('节点方法');
                $blueprint->smallInteger('sort')->comment('节点排序')->default(255);
                $blueprint->string('class_name', 255)->ccAscii()->comment('节点类名');
                $blueprint->string('alias_name', 128)->comment('节点别名');
                $blueprint->string('description', 255)->comment('节点描述');
                $blueprint->integer('flags')->comment('选项标识');
                $blueprint->unique('hash');
                $blueprint->index(['pid', 'genre']);
                $blueprint->index(['pid', 'sort']);
                $blueprint->unique('lkey')->limit(32);
                $blueprint->unique('nkey')->limit(48);
            });
        });
    }
}
