<?php

namespace Database\Seeders;

use App\Models\MuscleSubdivision;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeedMuslceSundivisionTable extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('muscle_subdivisions')->truncate();
        Schema::enableForeignKeyConstraints();

        $subdivisions = [
            // Chest
            ['id' => 'chest_upper', 'name' => 'Upper (Clavicular)', 'muscle_id' => 'chest'],
            ['id' => 'chest_middle', 'name' => 'Middle (Sternal)', 'muscle_id' => 'chest'],
            ['id' => 'chest_lower', 'name' => 'Lower (Costal)', 'muscle_id' => 'chest'],

            // Lats
            ['id' => 'lats_upper', 'name' => 'Upper/Width', 'muscle_id' => 'lats'],
            ['id' => 'lats_lower', 'name' => 'Lower/Thickness', 'muscle_id' => 'lats'],

            // Middle Back
            ['id' => 'middle_back_rhomboids', 'name' => 'Rhomboids', 'muscle_id' => 'middle_back'],
            ['id' => 'middle_back_traps', 'name' => 'Mid-Traps', 'muscle_id' => 'middle_back'],

            // Quadriceps
            ['id' => 'quads_outer', 'name' => 'Outer Sweep (Vastus Lateralis)', 'muscle_id' => 'quadriceps'],
            ['id' => 'quads_inner', 'name' => 'Inner (Teardrop)', 'muscle_id' => 'quadriceps'],
            ['id' => 'quads_rectus_femoris', 'name' => 'Rectus Femoris', 'muscle_id' => 'quadriceps'],

            // Shoulders
            ['id' => 'shoulders_front', 'name' => 'Front (Anterior)', 'muscle_id' => 'shoulders'],
            ['id' => 'shoulders_side', 'name' => 'Side (Lateral)', 'muscle_id' => 'shoulders'],
            ['id' => 'shoulders_rear', 'name' => 'Rear (Posterior)', 'muscle_id' => 'shoulders'],

            // Hamstrings
            ['id' => 'hamstrings_medial', 'name' => 'Medial (Inner)', 'muscle_id' => 'hamstrings'],
            ['id' => 'hamstrings_lateral', 'name' => 'Lateral (Outer)', 'muscle_id' => 'hamstrings'],

            // Glutes
            ['id' => 'glutes_maximus', 'name' => 'Maximus (Main)', 'muscle_id' => 'glutes'],
            ['id' => 'glutes_medius', 'name' => 'Medius (Side/Upper)', 'muscle_id' => 'glutes'],
            ['id' => 'glutes_minimus', 'name' => 'Minimus', 'muscle_id' => 'glutes'],

            // Biceps
            ['id' => 'biceps_long', 'name' => 'Long Head (Outer)', 'muscle_id' => 'biceps'],
            ['id' => 'biceps_short', 'name' => 'Short Head (Inner)', 'muscle_id' => 'biceps'],
            ['id' => 'biceps_brachialis', 'name' => 'Brachialis', 'muscle_id' => 'biceps'],

            // Triceps
            ['id' => 'triceps_long', 'name' => 'Long Head (Back)', 'muscle_id' => 'triceps'],
            ['id' => 'triceps_lateral', 'name' => 'Lateral Head (Side)', 'muscle_id' => 'triceps'],
            ['id' => 'triceps_medial', 'name' => 'Medial Head', 'muscle_id' => 'triceps'],

            // Abdominals
            ['id' => 'abs_upper', 'name' => 'Upper Abs', 'muscle_id' => 'abdominals'],
            ['id' => 'abs_lower', 'name' => 'Lower Abs', 'muscle_id' => 'abdominals'],
            ['id' => 'abs_obliques', 'name' => 'Obliques (Sides)', 'muscle_id' => 'abdominals'],

            // Calves
            ['id' => 'calves_gastrocnemius', 'name' => 'Gastrocnemius (Outer)', 'muscle_id' => 'calves'],
            ['id' => 'calves_soleus', 'name' => 'Soleus (Inner/Lower)', 'muscle_id' => 'calves'],

            // Traps
            ['id' => 'traps_upper', 'name' => 'Upper Traps', 'muscle_id' => 'traps'],
            ['id' => 'traps_lower', 'name' => 'Lower Traps', 'muscle_id' => 'traps'],

            // Forearms
            ['id' => 'forearms_flexors', 'name' => 'Flexors (Inside)', 'muscle_id' => 'forearms'],
            ['id' => 'forearms_extensors', 'name' => 'Extensors (Outside)', 'muscle_id' => 'forearms'],

            // Lower Back
            ['id' => 'lower_back_erector', 'name' => 'Erector Spinae', 'muscle_id' => 'lower_back'],
        ];

        foreach ($subdivisions as &$subdivision) {
            $subdivision['created_at'] = now();
            $subdivision['updated_at'] = now();
        }

        DB::table('muscle_subdivisions')->insert($subdivisions);
    }
}
