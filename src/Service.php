<?php
declare(strict_types=1);

namespace HZEX\Phinx;

use HZEX\Phinx\Command\Breakpoint;
use HZEX\Phinx\Command\Create;
use HZEX\Phinx\Command\ListAliases;
use HZEX\Phinx\Command\Migrate;
use HZEX\Phinx\Command\Rollback;
use HZEX\Phinx\Command\SeedCreate;
use HZEX\Phinx\Command\SeedRun;
use HZEX\Phinx\Command\Status;
use HZEX\Phinx\Command\Test;

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
