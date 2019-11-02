<?php
declare(strict_types=1);

namespace Phinx;

use think\Service;

class PhinxService extends Service
{
    public function boot()
    {
        $this->commands(PhinxCommand::class);
    }
}
