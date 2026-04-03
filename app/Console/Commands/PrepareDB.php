<?php

namespace App\Console\Commands;

use App\Models\Equipment;
use App\Models\ExercieMuscle;
use App\Models\Exercies;
use App\Models\Muscle;
use Database\Seeders\SeedMuslceSundivisionTable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Pest\Support\Str;

#[Signature('app:prepare-db')]
#[Description('Seed the workout database from the exercises JSON file.')]
class PrepareDB extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1- Read json file
        $file_path = public_path('exercises_v2.json');
        if (!file_exists($file_path)) {
            $this->error("File not found: $file_path");
            return;
        }
        $exercies_json = json_decode(file_get_contents($file_path));
        $this->info('Exercices count: ' . count($exercies_json));

        // 1- Extracting muscle groups
        $muscle_groups = [];
        foreach ($exercies_json as $exercice) {
            $muscle_groups = array_merge($exercice->primaryMuscles, $muscle_groups);
            $muscle_groups = array_merge($exercice->secondaryMuscles, $muscle_groups);
            $muscle_groups = array_unique($muscle_groups);
        }

        $muscle_metadata = [
            "chest" => ["cat" => "major", "side" => "front"],
            "lats" => ["cat" => "major", "side" => "back"],
            "middle_back" => ["cat" => "major", "side" => "back"],
            "quadriceps" => ["cat" => "major", "side" => "lower"],
            "shoulders" => ["cat" => "major", "side" => "front"],
            "hamstrings" => ["cat" => "major", "side" => "lower"],
            "glutes" => ["cat" => "major", "side" => "lower"],
            "biceps" => ["cat" => "minor", "side" => "front"],
            "triceps" => ["cat" => "minor", "side" => "back"],
            "abdominals" => ["cat" => "minor", "side" => "front"],
            "calves" => ["cat" => "minor", "side" => "lower"],
            "traps" => ["cat" => "minor", "side" => "back"],
            "forearms" => ["cat" => "minor", "side" => "front"],
            "lower_back" => ["cat" => "minor", "side" => "back"],
            "abductors" => ["cat" => "minor", "side" => "lower"],
            "adductors" => ["cat" => "minor", "side" => "lower"],
            "neck" => ["cat" => "minor", "side" => "back"],
        ];

        foreach ($muscle_groups as $muscle) {
            $muscle_id = str_replace('-', '_', Str::slugify($muscle));
            $this->info("Muscle: $muscle, slug: $muscle_id");

            $meta = $muscle_metadata[$muscle_id] ?? null;

            Muscle::updateOrCreate(
                ['id' => $muscle_id],
                [
                    'name' => $muscle,
                    'category' => $meta['cat'] ?? null,
                    'side' => $meta['side'] ?? null,
                ]
            );
        }

        // 2- Extraction of equipments
        $equipments = [];
        foreach ($exercies_json as $exercice) {
            $equipments[] = $exercice->equipment;
        }
        $equipments = array_unique($equipments);

        // 4- Seeding equipment
        foreach ($equipments as $equipment) {
            if ($equipment) {
                Equipment::firstOrCreate(
                    ['name' => $equipment]
                );
            }
        }

        // 5- Seeding exercies
        foreach ($exercies_json as $exercice) {
            $exercice_instrcution = implode("\n", $exercice->instructions);
            Exercies::updateOrCreate(['id' => strtolower($exercice->id)], [
                'name' => $exercice->name,
                'instructions' => $exercice_instrcution,
                'force' => $exercice->force,
                'level' => $exercice->level,
                'mechanic' => $exercice->mechanic,
                'popularity' => $exercice->popularity,
                'category' => $exercice->category, // Saved directly as string (cast to Enum in model)
                'exercise_family' => $exercice->exercise_family, // Saved directly as string (cast to Enum in model)
                'equipment_id' => Equipment::where('name', $exercice->equipment)->value('id'),
            ]);
        }

        // 6- Seeding exercies muscle relationsip
        foreach ($exercies_json as $exercice) {
            // Seeding primary
            foreach ($exercice->primaryMuscles as $primary_muscle) {

                $exercice_id = strtolower($exercice->id);
                $muscle_id = str_replace('-', '_', Str::slugify($primary_muscle));

                ExercieMuscle::firstOrCreate(
                    ['exercie_id' => $exercice_id, 'muscle_id' => $muscle_id],
                    ["type" => 'primary']
                );
            }

            // Seeding secondary
            foreach ($exercice->secondaryMuscles as $secondary_muscle) {

                $exercice_id = strtolower($exercice->id);
                $muscle_id = str_replace('-', '_', Str::slugify($secondary_muscle));

                ExercieMuscle::firstOrCreate(
                    ['exercie_id' => $exercice_id, 'muscle_id' => $muscle_id],
                    ["type" => 'secondary']
                );
            }
        }

        // 7- Seeding submuscles
        (new SeedMuslceSundivisionTable())->run();

        // 8- Seeding exercies muscle subdivisions relationship
        $subdivision_mapping = [
            'upper-abs' => 'abs_upper',
            'lower-abs' => 'abs_lower',
            'obliques-sides' => 'abs_obliques',
            'upper-clavicular' => 'chest_upper',
            'middle-sternal' => 'chest_middle',
            'lower-costal' => 'chest_lower',
            'upper-width' => 'lats_upper',
            'lower-thickness' => 'lats_lower',
            'rhomboids' => 'middle_back_rhomboids',
            'mid-traps' => 'middle_back_traps',
            'outer-sweep-vastus-lateralis' => 'quads_outer',
            'inner-teardrop' => 'quads_inner',
            'rectus-femoris' => 'quads_rectus_femoris',
            'front-anterior' => 'shoulders_front',
            'side-lateral' => 'shoulders_side',
            'rear-posterior' => 'shoulders_rear',
            'medial-inner' => 'hamstrings_medial',
            'lateral-outer' => 'hamstrings_lateral',
            'maximus-main' => 'glutes_maximus',
            'medius-side-upper' => 'glutes_medius',
            'minimus' => 'glutes_minimus',
            'long-head-outer' => 'biceps_long',
            'short-head-inner' => 'biceps_short',
            'brachialis' => 'biceps_brachialis',
            'long-head-back' => 'triceps_long',
            'lateral-head-side' => 'triceps_lateral',
            'medial-head' => 'triceps_medial',
            'gastrocnemius-outer' => 'calves_gastrocnemius',
            'soleus-inner-lower' => 'calves_soleus',
            'upper-traps' => 'traps_upper',
            'lower-traps' => 'traps_lower',
            'flexors-inside' => 'forearms_flexors',
            'extensors-outside' => 'forearms_extensors',
            'erector-spinae' => 'lower_back_erector',
        ];

        DB::table('exercie_subdivisions')->truncate();
        foreach ($exercies_json as $exercice) {
            $exercice_id = strtolower($exercice->id);

            // Primary Subdivisions
            if (isset($exercice->primarySubdivionMuscles)) {
                foreach ($exercice->primarySubdivionMuscles as $sub_name) {
                    $sub_id = $subdivision_mapping[$sub_name] ?? null;
                    if ($sub_id) {
                        DB::table('exercie_subdivisions')->updateOrInsert(
                            ['exercie_id' => $exercice_id, 'subdivision_id' => $sub_id, 'type' => 'primary'],
                            ['created_at' => now(), 'updated_at' => now()]
                        );
                    }
                }
            }

            // Secondary Subdivisions
            if (isset($exercice->secondarySubdivionMuscles)) {
                foreach ($exercice->secondarySubdivionMuscles as $sub_name) {
                    $sub_id = $subdivision_mapping[$sub_name] ?? null;
                    if ($sub_id) {
                        DB::table('exercie_subdivisions')->updateOrInsert(
                            ['exercie_id' => $exercice_id, 'subdivision_id' => $sub_id, 'type' => 'secondary'],
                            ['created_at' => now(), 'updated_at' => now()]
                        );
                    }
                }
            }
        }
    }
}
