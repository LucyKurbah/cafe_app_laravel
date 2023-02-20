<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       DB::table('items')->insert([
            'item_name' => 'American Platter',
            'item_price' => '380.00',
            'item_desc' => 'American Platter'
       ]); 
    }
}
