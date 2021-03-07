<?php
namespace TestMigrations;

use HZEX\Phinx\Schema;
use Phinx\Migration\AbstractMigration;
use Zxin\Phinx\Schema\Blueprint;

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
            Schema::create('test_generated', function (Blueprint $blueprint) {
                $blueprint->comment = '订单菜品';
                $blueprint->unsigned = true;

                $blueprint->json('food')->comment('菜品快照');
                $blueprint->string('food_name', 64)
                    ->unsigned(true)
                    ->generated("json_unquote(json_extract(`food`,'$.food_name'))")
                    ->comment('菜品名称');
                $blueprint->integer('price')
                    ->generated("json_extract(`food`,'$.price')")
                    ->comment('单价');
                $blueprint->string('spec', 255)->unsigned(true)
                    ->generated("json_unquote(json_extract(`food`,'$.spec'))")
                    ->comment('菜品规格名');
                $blueprint->text('food_unit')->nullable(true)->unsigned(true)
                    ->generated("json_unquote(json_extract(`food`,'$.food_unit'))")
                    ->comment('单位量词');
                $blueprint->integer('not_discount')
                    ->generated("json_extract(`food`,'$.not_discount')")
                    ->unsigned(true)
                    ->comment('是否打折: 1.是 2.否');
            });


            Schema::create('system', function (Blueprint $blueprint) {
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
                $blueprint->unsignedTinyInteger('is_home')->generated('`int` & 1 > 0');
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

            Schema::create('permission', function (Blueprint $blueprint) {
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
                $blueprint->index(['pid', 'genre'])->order(['pid' => 'ASC', 'genre' => 'DESC']);
                $blueprint->index(['pid', 'sort']);
                $blueprint->unique('lkey')->limit(32);
                $blueprint->unique('nkey')->limit(48);
            });
        });
    }
}
