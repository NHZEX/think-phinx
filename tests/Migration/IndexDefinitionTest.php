<?php

namespace Test\Phinx\Migration;

use PHPUnit\Framework\TestCase;
use ValueError;
use Zxin\Phinx\Schema\Definition\IndexDefinition;

class IndexDefinitionTest extends TestCase
{
    public function testOrder()
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('order value can only be DESC or ASC');

        $inde = new IndexDefinition('index');
        $inde->order([
            'index' => 'ASD',
        ]);
    }
}
