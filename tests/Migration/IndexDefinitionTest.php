<?php

namespace Test\Phinx\Migration;

use HZEX\Phinx\Schema\IndexDefinition;
use PHPUnit\Framework\TestCase;
use ValueError;

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
