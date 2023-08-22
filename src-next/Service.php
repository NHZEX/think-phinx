<?php
declare(strict_types=1);

namespace Zxin\Think\Phinx;

use Zxin\Think\Phinx\Command\Breakpoint;
use Zxin\Think\Phinx\Command\Create;
use Zxin\Think\Phinx\Command\ListAliases;
use Zxin\Think\Phinx\Command\Migrate;
use Zxin\Think\Phinx\Command\Rollback;
use Zxin\Think\Phinx\Command\SeedCreate;
use Zxin\Think\Phinx\Command\SeedRun;
use Zxin\Think\Phinx\Command\Status;
use Zxin\Think\Phinx\Command\Test;

class Service extends \think\Service
{
    public function register()
    {
        $this->commands([
            Create::getDefaultName()      => Create::class,
            Migrate::getDefaultName()     => Migrate::class,
            Rollback::getDefaultName()    => Rollback::class,
            Status::getDefaultName()      => Status::class,
            Breakpoint::getDefaultName()  => Breakpoint::class,
            Test::getDefaultName()        => Test::class,
            SeedCreate::getDefaultName()  => SeedCreate::class,
            SeedRun::getDefaultName()     => SeedRun::class,
            ListAliases::getDefaultName() => ListAliases::class,
        ]);
    }
}
