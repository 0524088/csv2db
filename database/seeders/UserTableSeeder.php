<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Hash;
use DB;
use Carbon\Carbon;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('users')->insert([
            'account' => 'test',
            'password' => Hash::make('1234'),
            'create_at' => Carbon::now(),
            'update_at' => Carbon::now()
        ]);
    }
}
