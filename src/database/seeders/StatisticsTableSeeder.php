<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class StatisticsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Statistic::class, 50)->create();
    }
}
