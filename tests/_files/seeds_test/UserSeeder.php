<?php

use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'username' => 'qwe',
                'password' => 'asd',
                'email' => 'zxc@email.net',
                'created' => date('Y-m-d H:i:s'),
            ],
            [
                'username' => 'rty',
                'password' => 'fgh',
                'email' => 'cvb@email.net',
                'created' => date('Y-m-d H:i:s'),
            ]
        ];

        $posts = $this->table('users');
        $posts->insert($data)
              ->save();
    }
}
