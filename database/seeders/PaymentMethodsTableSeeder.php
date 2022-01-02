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
        \Illuminate\Support\Facades\DB::table('payment_methods')
            ->insert(array('name' => 'Paypal', 'slug' => 'paypal'));
        \Illuminate\Support\Facades\DB::table('payment_methods')
            ->insert(array('name' => 'Pay on delivery', 'slug' => 'cash'));
    }
}
