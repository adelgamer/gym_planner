<?php

namespace App\Services;

use App\Enums\Difficulty;
use App\Enums\ExercieCategory;
use App\Enums\EquipmentSelection;

class WorkoutPreferences
{
    public function __construct(
        public EquipmentSelection $equipmentSelection = EquipmentSelection::ALL,
        public float $topP = 0.7,
        public Difficulty $difficulty = Difficulty::ALL,
        public bool $fullExerciseObject = false,
        public ExercieCategory $exercieCategory = ExercieCategory::ALL
    ) {}
}
