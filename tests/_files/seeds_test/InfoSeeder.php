<?php
declare(strict_types=1);

class InfoSeeder extends \Phinx\Seed\AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'id' => 1,
                'username' => 'qwerty',
            ],
            [
                'id' => 2,
                'username' => 'asdfgh',
            ],
            [
                'id' => 3,
                'username' => 'zxcvbn',
            ]
        ];

        $posts = $this->table('info');
        $posts->insert($data)
            ->save();
    }
}
