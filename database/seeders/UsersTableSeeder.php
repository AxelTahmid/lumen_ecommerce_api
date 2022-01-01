<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use  Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // create super admin user
        DB::table('users')->insert(array('name' => 'super', 'email' => 'super@tahmid.com', 'password' => app('hash')->make('tahmid12345'), 'is_super_admin' => 1));
    }
}
