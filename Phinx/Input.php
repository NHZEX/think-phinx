<?php
declare(strict_types=1);

namespace Phinx;

use RuntimeException;

class Input extends \think\console\Input
{
    protected function parse(): void
    {
        return;
    }

    public function validate()
    {
        return;
    }
}
