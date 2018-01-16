<?php

namespace Hanoivip\Game\Database\Seeds;

use Hanoivip\Game\Server;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WlyServersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Server::query()->truncate();
        DB::table('servers')->insert([
            [
                'name' => 's59',
                'ident' => 59,
                'title' => 'Tào tháo',
                'description' => 'Test tích hợp Wly',
                'login_uri' => 's59.ngoalongvn.us',
                'recharge_uri' => 'gs59.ngoalongvn.us:8080',
                'operate_uri' => 'gs59.ngoalongvn.us:8080',
            ],
        ]);
    }
}
