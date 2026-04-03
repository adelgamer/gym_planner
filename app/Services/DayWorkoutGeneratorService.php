<?php

namespace App\Services;

use App\Enums\Difficulty;
use App\Enums\ExercieCategory;
use App\Enums\StartWithExercise;
use App\Models\Muscle;
use Exception;
use Illuminate\Support\Collection;

class DayWorkoutGeneratorService
{
    private array $muscleIds;
    private int $exerciseCount;
    private Collection $plan;
    private array $numbering = [];
    private Collection $musclesData;
    private const MAX_ATTEMPT = 25;

    /**
     * DayWorkoutGeneratorService constructor.
     * 
     * @param array $muscleIds List of muscle IDs to include (1-3)
     * @param int $exerciseCount Total number of exercises (3-8)
     */
    public function __construct(array $muscleIds, int $exerciseCount,  private array $exerciceIdsToExclude = [], private WorkoutPreferences $prefs)
    {
        $this->muscleIds = $muscleIds;
        $this->exerciseCount = $exerciseCount;
        $this->plan = collect([]);
    }

    /**
     * The main entry point to generate the workout plan.
     * It follows a step-by-step process to build the final list of exercises.
     */
    public function generate(): Collection
    {
        // Step 1: Check if the number of muscles and exercises are valid
        // $this->validate();

        // Step 2: Decide how many exercises each muscle group (Chest, Biceps, etc.) gets
        $this->calculateNumbering();

        // Step 3: Fetch muscle details and their exercises (primary only) from the database
        $this->loadMusclesData();

        // Step 4: Pick the actual exercises based on the calculated counts
        $this->buildPlan();

        return $this->plan;
    }

    /**
     * Ensures parameters are within allowed limits (e.g., 1-3 muscle groups, 3-8 exercises).
     */
    private function validate(): void
    {
        $muscleCount = count($this->muscleIds);
        if ($muscleCount > 3 || $muscleCount < 1) {
            throw new Exception('Groups muscles must be between 1 -> 3');
        }
        if ($this->exerciseCount < 3 || $this->exerciseCount > 8) {
            throw new Exception('Exercises count must be between 3 -> 8');
        }
    }

    /**
     * Logic for distributing exercises across the chosen muscle groups.
     * It prioritizes 'Major' muscles (like Chest/Back) over 'Minor' ones (like Biceps).
     */
    private function calculateNumbering(): void
    {
        // Sort muscles so majors are processed first
        $muscles = Muscle::whereIn('id', $this->muscleIds)->orderBy('category', 'asc')->get();

        if ($muscles->isEmpty()) {
            throw new Exception("No valid muscles found in database for IDs: " . implode(', ', $this->muscleIds));
        }

        $majorIds = [];
        $minorIds = [];

        // Group IDs by their size category
        foreach ($muscles as $muscle) {
            $this->numbering[$muscle->id] = ["type" => $muscle->category, "exercies_number" => 0];
            if ($muscle->category === 'major') {
                $majorIds[] = $muscle->id;
            } else {
                $minorIds[] = $muscle->id;
            }
        }

        $numberOfMajors = count($majorIds);
        $numberOfMinors = count($minorIds);

        // Limit exercises for minor-only sessions to prevent overtraining
        $exerciseCount = $this->exerciseCount;
        if ($numberOfMajors === 0 && $exerciseCount > 6) {
            $exerciseCount = 6;
        }

        $majorSlotsTotal = 0;

        // Determine the split between major and minor muscles
        if ($numberOfMajors > 0 && $numberOfMinors > 0) {
            // Mixed session: balancing heavy lifting with isolation
            if ($exerciseCount <= 5) {
                // Short workout: mostly majors
                $majorSlotsTotal = ($numberOfMajors === 1) ? 3 : 4;
            } else {
                // Long workout: 5-6 slots for majors
                if ($numberOfMajors === 1) {
                    $majorSlotsTotal = 5;
                } elseif ($numberOfMajors === 2) {
                    $majorSlotsTotal = 5;
                } else {
                    $majorSlotsTotal = 6;
                }
            }
            // Ensure every minor muscle gets at least 1 exercise
            $majorSlotsTotal = min($majorSlotsTotal, $exerciseCount - $numberOfMinors);
        } elseif ($numberOfMajors > 0) {
            // Major-only session: all slots go to majors
            $majorSlotsTotal = $exerciseCount;
        }

        // What's left goes to minor muscles
        $minorSlotsTotal = $exerciseCount - $majorSlotsTotal;

        // Distribute major slots evenly (Round-Robin)
        for ($i = 0; $i < $majorSlotsTotal; $i++) {
            $id = $majorIds[$i % $numberOfMajors];
            $this->numbering[$id]['exercies_number']++;
        }

        // Distribute minor slots evenly (Round-Robin)
        for ($i = 0; $i < $minorSlotsTotal; $i++) {
            $id = $minorIds[$i % $numberOfMinors];
            $this->numbering[$id]['exercies_number']++;
        }
    }

    /**
     * Retrieves muscle data plus related exercises and their subdivisions for filtering.
     */
    private function loadMusclesData(): void
    {
        $this->musclesData = Muscle::whereIn('id', $this->muscleIds)->with(['exercies' => function ($query) {
            // Only pull primary exercises to keep the workout focused
            $query->wherePivot('type', 'primary')->with(['muscleSubdivisions', 'equipment']);
        }])->get();
    }

    /**
     * Loops through the muscle-exercise distribution and picks the exercises.
     */
    private function buildPlan(): void
    {
        foreach ($this->numbering as $key => $value) {
            $muscle = $this->musclesData->where('id', $key)->first();

            // If this muscle is scheduled to have exercises, pick them
            if ($muscle && $value['exercies_number'] > 0) {
                $musclePlan = $this->pickRandomExercises($muscle->exercies, $value['exercies_number'], $this->prefs->topP);
                // Keep adding to the master plan collection
                $this->plan = $this->plan->concat($musclePlan);
            }
        }
    }

    /**
     * The core picking logic. It handles quality filtering (topP) and variety (subdivisions).
     */
    private function pickRandomExercises($exercises, int $choices, float $top_p, int $attempt = 0): Collection
    {
        // Check which parts of muscles (e.g. Upper Chest, Lower Abs) are already targeted
        $submusclesHit = $this->plan->flatMap(function ($exercise) {
            return $exercise->muscleSubdivisions->pluck('id');
        })->unique();
        // print_r($submusclesHit->toArray());

        // Filter 1: Reliability & Preferences. Keep only exercises that meet the 'top_p' popularity threshold, 
        // equipment preferences, and the selected difficulty level.
        $filtered = $exercises->reject(function ($ex) use ($top_p) {
            $lowPopularity = ($ex->popularity / 10) < $top_p; // Skip if exercise popularity is below threshold

            // Use the new EquipmentSelection enum to decide if the equipment is allowed
            $disallowBasedEquipment = !in_array($ex->equipment_id, $this->prefs->equipmentSelection->getAllowedIds());

            $disallowBasedDifficulty = false;
            // Filter by difficulty level if it's set to something other than 'ALL'
            if ($this->prefs->difficulty !== Difficulty::ALL) {
                // Must compare string from DB against enum value
                $disallowBasedDifficulty = $ex->level !== $this->prefs->difficulty->value;
            }

            $disallowBasedCategory = false;
            // Filter by category level if it's set to something other than 'ALL'
            if ($this->prefs->exercieCategory !== ExercieCategory::ALL) {
                // Since $ex->category is cast to ExercieCategory enum in the model, compare directly.
                $disallowBasedCategory = $ex->category !== $this->prefs->exercieCategory;
            }

            return $lowPopularity || $disallowBasedEquipment || $disallowBasedDifficulty || $disallowBasedCategory;
        });

        // Filter 2: Variety. Try to pick exercises that don't hit the same sub-area twice.
        $nonRepeating = $filtered->reject(function ($ex) use ($submusclesHit) {
            return $ex->muscleSubdivisions->pluck('id')->intersect($submusclesHit)->isNotEmpty();
        });

        // If we found enough 'unique' exercises, use them. 
        // Otherwise, fall back to the basic filtered list to fulfill the count.
        if ($nonRepeating->count() >= $choices) {
            $filtered = $nonRepeating;
        } else if ($nonRepeating->count() < $choices && $top_p > 0) {
            return $this->pickRandomExercises($exercises, $choices, $top_p - 0.1);
        }
        $top_p = $this->prefs->topP;

        // Return a random selection from the best available candidates
        $randomExercises = $filtered->random(min($choices, $filtered->count()));

        // Choose unique exercieses from the entire week
        foreach ($randomExercises as $ex) {
            if (in_array($ex->id, $this->exerciceIdsToExclude) && $attempt < self::MAX_ATTEMPT) {
                $attempt++;
                return $this->pickRandomExercises($exercises, $choices, $top_p, $attempt);
            }
            $this->exerciceIdsToExclude[] = $ex->id;
        }
        $attempt = 0;

        if ($randomExercises->count() === 0 && $top_p > 0) {
            return $this->pickRandomExercises($exercises, $choices, $top_p - 0.1);
        }
        $top_p = $this->prefs->topP;

        // If there is no single isolation exercise then recreate
        if (!in_array(StartWithExercise::ISOLATION, $randomExercises->pluck('mechanic')->toArray()) && $attempt < self::MAX_ATTEMPT) {
            $attempt++;
            return $this->pickRandomExercises($exercises, $choices, $top_p, $attempt);
        }
        $attempt = 0;

        // Order compound and isolation
        if ($this->prefs->startWithExercise !== StartWithExercise::ALL) {
            if ($this->prefs->startWithExercise === StartWithExercise::COMPOUND) {
                $randomExercises = $randomExercises->sortBy('mechanic');
            } else {
                $randomExercises = $randomExercises->sortByDesc('mechanic');
            }
        }


        return $randomExercises;
    }
}
