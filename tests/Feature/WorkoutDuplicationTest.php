<?php

use App\Services\WeekPlanGenerator;
use App\Services\WorkoutPreferences;
use App\Enums\Difficulty;
use App\Enums\EquipmentSelection;
use Tests\TestCase;

class WorkoutDuplicationTest extends TestCase
{
    /**
     * Test that no exercise is duplicated in a 3-day workout plan.
     */
    public function test_no_exercises_are_duplicated_in_a_3_day_plan(): void
    {
        // 1. Setup preferences (get full objects to check IDs easily)
        $prefs = new WorkoutPreferences(
            difficulty: Difficulty::ALL,
            equipmentSelection: EquipmentSelection::ALL,
            topP: 0.0, // Set to 0 to include more exercises and increase collision chance
            fullExerciseObject: true
        );

        // 2. Generate a 3-day plan
        $daysPerWeek = 3;
        $generator = new WeekPlanGenerator($daysPerWeek, $prefs);
        $plan = $generator->generate();

        // 3. Extract all exercise IDs from all days
        $allExerciseIds = [];
        foreach ($plan as $dayKey => $dayData) {
            foreach ($dayData['exercies'] as $exercise) {
                // If fullExerciseObject is true, $exercise is an array/object with an 'id'
                $allExerciseIds[] = is_array($exercise) ? $exercise['id'] : $exercise->id;
            }
        }

        // 4. Verification
        $uniqueIds = array_unique($allExerciseIds);
        
        $this->assertNotEmpty($allExerciseIds, "The generator returned 0 exercises. Ensure your database is seeded.");
        $this->assertEquals(
            count($allExerciseIds), 
            count($uniqueIds), 
            sprintf(
                "Duplicate exercises found! Total: %d, Unique: %d. Duplicates: %s",
                count($allExerciseIds),
                count($uniqueIds),
                implode(', ', array_diff_assoc($allExerciseIds, $uniqueIds))
            )
        );
    }

    /**
     * Test with very restricted equipment to force the generator to potentially pick same exercises
     */
    public function test_no_duplicates_even_with_restricted_equipment(): void
    {
        $prefs = new WorkoutPreferences(
            difficulty: Difficulty::ALL,
            equipmentSelection: EquipmentSelection::DUMBBELLS, // Restricted pool
            topP: 0.0,
            fullExerciseObject: true
        );

        $generator = new WeekPlanGenerator(3, $prefs);
        $plan = $generator->generate();

        $allExerciseIds = [];
        foreach ($plan as $dayKey => $dayData) {
            foreach ($dayData['exercies'] as $exercise) {
                $allExerciseIds[] = is_array($exercise) ? $exercise['id'] : $exercise->id;
            }
        }

        $uniqueIds = array_unique($allExerciseIds);
        
        $this->assertEquals(count($allExerciseIds), count($uniqueIds));
    }
}
