<?php

namespace App\Http\Controllers;

use App\Statistic;
use App\Http\Resources\StatisticCollection;

class StatisticsController extends ApiController
{
    public function index()
    {
        $count = Statistic::count();
        $take = 100;
        $skip = ($count > $take) ? $count - $take : 0;
        return new StatisticCollection(Statistic::oldest()->skip($skip)->take($take)->get());
    }
}
