<?php

namespace App\Services;

use App\Models\Muscle;
use Exception;
use Illuminate\Support\Collection;

class WorkoutGeneratorService
{
    private array $muscleIds;
    private int $exerciseCount;
    private float $topP;
    private Collection $plan;
    private array $numbering = [];
    private Collection $musclesData;

    /**
     * WorkoutGeneratorService constructor.
     * 
     * @param array $muscleIds List of muscle IDs to include (1-3)
     * @param int $exerciseCount Total number of exercises (3-8)
     * @param float $topP Popularity threshold (0.0-1.0)
     */
    public function __construct(array $muscleIds, int $exerciseCount, float $topP = 0.7)
    {
        $this->muscleIds = $muscleIds;
        $this->exerciseCount = $exerciseCount;
        $this->topP = $topP;
        $this->plan = collect([]);
    }

    /**
     * The main entry point to generate the workout plan.
     * It follows a step-by-step process to build the final list of exercises.
     */
    public function generate(): Collection
    {
        // Step 1: Check if the number of muscles and exercises are valid
        $this->validate();

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
            $query->wherePivot('type', 'primary')->with('muscleSubdivisions');
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
                $musclePlan = $this->pickRandomExercises($muscle->exercies, $value['exercies_number']);
                // Keep adding to the master plan collection
                $this->plan = $this->plan->concat($musclePlan);
            }
        }
    }

    /**
     * The core picking logic. It handles quality filtering (topP) and variety (subdivisions).
     */
    private function pickRandomExercises($exercises, int $choices): Collection
    {
        // Check which parts of muscles (e.g. Upper Chest, Lower Abs) are already targeted
        $submusclesHit = $this->plan->flatMap(function ($exercise) {
            return $exercise->muscleSubdivisions->pluck('id');
        })->unique();

        // Filter 1: Reliability. Keep only exercises that meet your 'top_p' popularity threshold.
        $filtered = $exercises->reject(fn($ex) => (($ex->popularity) / 10) < $this->topP);

        // Filter 2: Variety. Try to pick exercises that don't hit the same sub-area twice.
        $nonRepeating = $filtered->reject(function ($ex) use ($submusclesHit) {
            return $ex->muscleSubdivisions->pluck('id')->intersect($submusclesHit)->isNotEmpty();
        });

        // If we found enough 'unique' exercises, use them. 
        // Otherwise, fall back to the basic filtered list to fulfill the count.
        if ($nonRepeating->count() >= $choices) {
            $filtered = $nonRepeating;
        }

        // Return a random selection from the best available candidates
        return $filtered->random(min($choices, $filtered->count()));
    }
}
