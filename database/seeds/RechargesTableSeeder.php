<?php

namespace Hanoivip\Game\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RechargesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('recharges')->insert([
            'code' => 'test',
            'title' => 'Test nap 10k',
            'coin' => 10000,
            'coin_type' => 0,
        ]);
    }
}
