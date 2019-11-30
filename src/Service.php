<?php
declare(strict_types=1);

namespace HZEX\Phinx;

use HZEX\Phinx\Command\Breakpoint;
use HZEX\Phinx\Command\Create;
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
//        $this->commands([
//            Breakpoint::getDefaultName() => new Breakpoint(),
//            Status::getDefaultName()     => new Status(),
//        ]);

        $this->commands([
            new Create(),
            new Migrate(),
            new Rollback(),
            new Status(),
            new Breakpoint(),
            new Test(),
            new SeedCreate(),
            new SeedRun(),
        ]);
    }
}
