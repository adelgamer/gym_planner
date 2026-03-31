<?php

namespace App\Http\Controllers;

use App\Models\Muscle;
use Exception;
use Illuminate\Http\Request;

use App\Services\DayWorkoutGeneratorService;

class PlanGeneratorController extends Controller
{
    /**
     * Generate a workout plan using the stateful DayWorkoutGeneratorService.
     * 
     * @param array $muscle_ids
     * @param int $exercies_count
     * @param float $top_p
     * @return \Illuminate\Support\Collection
     */
    public static function generate_day_plan(array $muscle_ids = [], int $exercies_count = 3, float $top_p = 0.7)
    {
        $service = new DayWorkoutGeneratorService($muscle_ids, $exercies_count, $top_p);

        return $service->generate();
    }
}
