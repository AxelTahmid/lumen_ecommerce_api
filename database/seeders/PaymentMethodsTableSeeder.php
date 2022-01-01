<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use  Illuminate\Support\Facades\DB;

class PaymentMethodsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('payment_methods')->insert(array('name' => 'Paypal', 'slug' => 'paypal'));
        DB::table('payment_methods')->insert(array('name' => 'Cash on delivery', 'slug' => 'cash'));
    }
}
