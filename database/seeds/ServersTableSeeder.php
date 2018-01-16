<?php

namespace Hanoivip\Game\Database\Seeds;

use Hanoivip\Game\Server;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Server::query()->truncate();
        for ($i=1; $i<94; ++$i)
        {
            DB::table('servers')->insert([
                'name' => 's' . $i,
                'ident' => $i,
                'title' => str_random(),
                'description' => str_random(100),
                'login_uri' => 's' . $i . '.game.test',
                'recharge_uri' => 's' . $i . '.game.test',
                'operate_uri' => 's' . $i . '.game.test',
            ]);
        }
    }
}
