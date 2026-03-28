<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Signature('gym:map-popularity')]
#[Description('Map exercise popularity from exercise_popularity.json to exercises.json')]
class MapExercisePopularity extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $exercisesPath = public_path('exercises.json');
        $popularityPath = public_path('exercise_popularity.json');

        if (!File::exists($exercisesPath)) {
            $this->error("File not found: {$exercisesPath}");
            return Command::FAILURE;
        }

        if (!File::exists($popularityPath)) {
            $this->error("File not found: {$popularityPath}");
            return Command::FAILURE;
        }

        $exercises = json_decode(File::get($exercisesPath), true);
        $popularityData = json_decode(File::get($popularityPath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Failed to parse JSON files.');
            return Command::FAILURE;
        }

        $this->info('Mapping popularity data...');

        // Create a lookup map for popularity (lowercase ID => popularity)
        $popularityMap = [];
        foreach ($popularityData as $item) {
            $id = strtolower($item['id']);
            $popularityMap[$id] = $item['popularity'];
        }

        $updatedCount = 0;
        foreach ($exercises as &$exercise) {
            $id = strtolower($exercise['id']);
            if (isset($popularityMap[$id])) {
                $exercise['popularity'] = $popularityMap[$id];
                $updatedCount++;
            } else {
                // Optional: handle missing records? If popularity is missing, maybe default to 0
                $exercise['popularity'] = 0;
            }
        }

        File::put($exercisesPath, json_encode($exercises, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $this->info("Successfully mapped popularity for {$updatedCount} exercises.");
        $this->info("Updated file saved at: {$exercisesPath}");

        return Command::SUCCESS;
    }
}

