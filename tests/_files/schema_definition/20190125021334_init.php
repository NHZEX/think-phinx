<?php
namespace TestMigrations;

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
                $blueprint->integer('int');
                $blueprint->unsignedInteger('uint');
                $blueprint->smallInteger('sint');
                $blueprint->unsignedSmallInteger('usint');
                $blueprint->tinyInteger('tint');
                $blueprint->unsignedTinyInteger('utint');
                $blueprint->string('string', 255);
                $blueprint->char('char', 8);
                $blueprint->text('text');
                $blueprint->json('json');
                $blueprint->tinyInteger('is_home')->generated('`int` & 1 > 0');
                $blueprint->tinyInteger('is_popular')->generated('`int` & 2 > 0', true);
                $blueprint->lockVersion();
                $blueprint->createTime();
                $blueprint->updateTime();
                $blueprint->deleteTime();
                $blueprint->createBy();
                $blueprint->updateBy();
                $blueprint->uuid();
                $blueprint->status();
                $blueprint->genre();
                $blueprint->remark();
                $blueprint->float('float', 3, 2);
                $blueprint->double('double', 6, 5);
                $blueprint->decimal('decimal', 8, 6);
            });

            Schema::create('permission', function (Schema\Blueprint $blueprint) {
                $blueprint->comment = '权限';
                $blueprint->unsigned = true;

                $blueprint->column('blob', 'blob1');
                $blueprint->blob('blob2');
                $blueprint->unsignedInteger('pid')->comment('父节点ID');
                $blueprint->genre()->comment('节点类型');
                $blueprint->string('nkey', 128)->ccAscii()->comment('节点命名key');
                $blueprint->string('hash', 8)->ccAscii()->comment('节点命名hash');
                $blueprint->string('lkey', 64)->ccAscii()->comment('节点逻辑key');
                $blueprint->smallInteger('sort')->comment('节点排序')->default(255);
                $blueprint->unique('hash');
                $blueprint->index(['pid', 'genre']);
                $blueprint->index(['pid', 'sort']);
                $blueprint->unique('lkey')->limit(32);
                $blueprint->unique('nkey')->limit(48);
            });
        });
    }
}
