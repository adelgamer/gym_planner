<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\ExercieMuscle;
use App\Models\Exercies;
use App\Models\Muscle;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Pest\Support\Str;

#[Signature('app:prepare-db')]
#[Description('Command description')]
class PrepareDB extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1- Read json file
        $exercies_json = json_decode(file_get_contents(public_path('exercises.json')));
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


        // 2- all possible levels, category, equipement, force, mechanics
        $levels = [];
        $categories = [];
        $equipments = [];
        $forces = [];
        $mechanics = [];
        foreach ($exercies_json as $exercice) {
            $levels[] = $exercice->level;
            $categories[] = $exercice->category;
            $equipments[] = $exercice->equipment;
            $forces[] = $exercice->force;
            $mechanics[] = $exercice->mechanic;
        }

        $levels = array_unique($levels);
        $categories = array_unique($categories);
        $equipments = array_unique($equipments);
        $forces = array_unique($forces);
        $mechanics = array_unique($mechanics);

        $this->info("\nAll unique levels identified: ");
        print_r($levels);
        $this->info("\nAll unique categories identified: ");
        print_r($categories);
        $this->info("\nAll unique equipment identified: ");
        print_r($equipments);
        $this->info("\nAll unique forces identified: ");
        print_r($forces);
        $this->info("\nAll unique mechanics identified: ");
        print_r($mechanics);

        // 3- Seeding categories
        foreach ($categories as $category) {
            if ($category) {
                Category::firstOrCreate(
                    ['name' => $category]
                );
            }
        }

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
            $seeded_exercice = Exercies::firstOrCreate(['id' => strtolower($exercice->id)], [
                'name' => $exercice->name,
                'instructions' => $exercice_instrcution,
                'force' => $exercice->force,
                'level' => $exercice->level,
                'mechanic' => $exercice->mechanic,
                'popularity' => $exercice->popularity,
                'category_id' => Category::where('name', $exercice->category)->value('id'),
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
    }
}
