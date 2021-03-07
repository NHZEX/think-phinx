<?php

namespace TestMigrations;

use HZEX\Phinx\Schema;
use Phinx\Migration\AbstractMigration;
use Zxin\Phinx\Schema\Blueprint;

class RemoveColumn extends AbstractMigration
{
    public function up()
    {
        Schema::cxt($this, function () {
            Schema::save('system', function (Blueprint $blueprint) {
                $blueprint->table->removeColumn('text');
                $blueprint->table->removeColumn('json');
            });
        });
    }

    public function down()
    {
        Schema::cxt($this, function () {
            Schema::update('system', function (Blueprint $blueprint) {
                $blueprint->json('json')->after('char');
                $blueprint->text('text')->after('char');
            });
        });
    }
}
