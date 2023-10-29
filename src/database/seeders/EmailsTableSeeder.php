<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class EmailsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Email::factory()->count(10)->create();
    }
}
