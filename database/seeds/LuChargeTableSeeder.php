<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LuChargeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('lu_charge')->insert([
            [
                'type' => "withdraw"
            ],
            [
                'type' => "fee"
            ],
            [
                'type' => "charge"
            ]
        ]);
    }
}
