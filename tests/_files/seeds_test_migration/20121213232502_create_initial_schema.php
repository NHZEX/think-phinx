<?php

namespace TestSeedTests;

use Phinx\Migration\AbstractMigration;

class CreateInitialSchema extends AbstractMigration
{
    /**
     * Change.
     */
    public function change()
    {
        // users table
        $users = $this->table('users');
        $users->addColumn('username', 'string', ['limit' => 20])
              ->addColumn('password', 'string', ['limit' => 40])
              ->addColumn('email', 'string', ['limit' => 100])
              ->addColumn('created', 'datetime')
              ->addColumn('updated', 'datetime', ['default' => '2000-01-01 00:00:00'])
              ->addIndex(['username', 'email'], ['unique' => true])
              ->create();

        // info table
        $info = $this->table('info');
        $info->addColumn('username', 'string', ['limit' => 20])
             ->create();
    }
}
