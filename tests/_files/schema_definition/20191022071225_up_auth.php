<?php

namespace TestMigrations;

use HZEX\Phinx\Schema;
use Phinx\Migration\AbstractMigration;

class UpAuth extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        Schema::cxt($this, function () {
            Schema::create('permission', function (Schema\Blueprint $blueprint) {
                $blueprint->table->drop()->save();

                $blueprint->comment = '权限';
                $blueprint->unsigned = true;

                $blueprint->genre()->comment('节点类型');
                $blueprint->smallInteger('sort')->comment('节点排序');
                $blueprint->string('name', 128)->ccAscii()->comment('权限名称');
                $blueprint->string('pid', 128)->ccAscii()->comment('父关联');
                $blueprint->json('control')->nullable(true)->comment('授权内容');
                $blueprint->string('desc', 512)->comment('权限描述');

                $blueprint->index('name')->limit(64);
            });

            Schema::save('system', function (Schema\Blueprint $blueprint) {
                $blueprint->string('string', 512)->comment('修改列')->change();
            });
        });
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        Schema::cxt($this, function () {
            Schema::save('permission', function (Schema\Blueprint $blueprint) {
                $blueprint->table->removeIndexByName('name');
                $blueprint->table->save();
            });
        });
    }
}
