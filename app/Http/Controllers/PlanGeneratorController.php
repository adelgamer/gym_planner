<?php

namespace App\Http\Controllers;

use App\Models\Muscle;
use Exception;
use Illuminate\Http\Request;

class PlanGeneratorController extends Controller
{
    // PlanGeneratorController::generate_day_plan(["chest", "shoulders", "triceps"], 3);
    private static function generate_exercie_numbering(array $muscle_ids = [], int $exercies_count = 3)
    {
        // 1- Validating input
        $muscle_count = count($muscle_ids);
        if ($muscle_count > 3 || $muscle_count < 1) {
            throw new Exception('Groups muscles must be between 1 -> 3');
        }
        if ($exercies_count < 3 || $exercies_count > 8) {
            throw new Exception('Exercieses count must be between 3 -> 8');
        }

        $muscles = Muscle::whereIn('id', $muscle_ids)->orderBy('category', 'asc')->get();
        // print_r($muscles->toArray());

        // --- WORKOUT GENERATION LOGIC (3-8 Exercises | 1-3 Muscle Groups) ---
        $muscles_and_counts = [];
        $major_ids = [];
        $minor_ids = [];

        foreach ($muscles as $muscle) {
            $muscles_and_counts[$muscle->id] = ["type" => $muscle->category, "exercies_number" => 0];
            if ($muscle->category === 'major') {
                $major_ids[] = $muscle->id;
            } else {
                $minor_ids[] = $muscle->id;
            }
        }

        $number_of_majors = count($major_ids);
        $number_of_minors = count($minor_ids);

        // 3. MINOR-ONLY WORKOUT (e.g., Arms/Abs)
        // Safety cap: Prevent overtraining small muscles
        if ($number_of_majors === 0 && $exercies_count > 6) {
            $exercies_count = 6;
        }

        $major_slots_total = 0;

        if ($number_of_majors > 0 && $number_of_minors > 0) {
            if ($exercies_count <= 5) {
                // 1. SMALL WORKOUT (3-5 Exercises)
                // Logic: Pick 2-3 Major exercises, remaining are Minor.
                $major_slots_total = ($number_of_majors === 1) ? 3 : 4;
            } else {
                // 2. LARGE WORKOUT (6-8 Exercises)
                // Logic: Pick 4-5 Major exercises, remaining are Minor.
                if ($number_of_majors === 1) {
                    $major_slots_total = 5;
                } elseif ($number_of_majors === 2) {
                    $major_slots_total = 5;
                } else {
                    $major_slots_total = 6; // 3 majors, "2-2-2" rule
                }
            }
            // Ensure minors get at least 1 slot per minor
            $major_slots_total = min($major_slots_total, $exercies_count - $number_of_minors);
        } elseif ($number_of_majors > 0) {
            // All majors
            $major_slots_total = $exercies_count;
        }

        // Remaining goes to minors
        $minor_slots_total = $exercies_count - $major_slots_total;

        // Distribute major slots using round-robin
        for ($i = 0; $i < $major_slots_total; $i++) {
            $id = $major_ids[$i % $number_of_majors];
            $muscles_and_counts[$id]['exercies_number']++;
        }

        // Distribute minor slots using round-robin
        for ($i = 0; $i < $minor_slots_total; $i++) {
            $id = $minor_ids[$i % $number_of_minors];
            $muscles_and_counts[$id]['exercies_number']++;
        }

        return $muscles_and_counts;
    }

    public static function generate_day_plan(array $muscle_ids = [], int $exercies_count = 3)
    {
        $muscles_exercies_numbering = self::generate_exercie_numbering($muscle_ids, $exercies_count);

        $muscles = Muscle::whereIn('id', $muscle_ids)->with(['exercies' => function ($query) {
            $query->wherePivot('type', 'primary');
        }])->get();

        $plan = [];
        foreach ($muscles_exercies_numbering as $key => $value) {
            $muscle = $muscles->where('id', $key)->first();

            if ($muscle && $value['exercies_number'] > 0) {
                $muscle_plan = $muscle->exercies->random($value['exercies_number'])->pluck('name')->toArray();
                $plan = array_merge($plan, $muscle_plan);
            }
        }

        return $plan;
    }
}
