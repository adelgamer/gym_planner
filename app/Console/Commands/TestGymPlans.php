<?php

namespace App\Console\Commands;

use App\Enums\Difficulty;
use App\Enums\EquipmentSelection;
use App\Services\WeekPlanGenerator;
use App\Services\WorkoutPreferences;
use Illuminate\Console\Command;

class TestGymPlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gym:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the gym plan generator with a matrix of Difficulty, topP, and Equipment settings.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dayCounts = [3]; // Focusing on 3-day plans as requested
        $difficulties = [Difficulty::ALL, Difficulty::BEGINNER];
        $topPs = [0.0,  1.0]; // Sweep from 0 to 1
        $equipments = [
            EquipmentSelection::ALL,
            EquipmentSelection::BODY_WEIGHT,
            EquipmentSelection::GYM,
            EquipmentSelection::DUMBBELLS
        ];

        foreach ($dayCounts as $days) {
            $this->info("==================================================");
            $this->info("          TESTING {$days} DAYS PER WEEK");
            $this->info("==================================================");

            foreach ($difficulties as $difficulty) {
                foreach ($equipments as $equipment) {
                    foreach ($topPs as $topP) {

                        $label = sprintf(
                            "Diff: %s | Eq: %s | topP: %.1f",
                            strtoupper($difficulty->value),
                            strtoupper($equipment->value),
                            $topP
                        );

                        $this->warn("\n[Scenario: {$label}]");

                        $prefs = new WorkoutPreferences(
                            difficulty: $difficulty,
                            equipmentSelection: $equipment,
                            topP: $topP,
                            fullExerciseObject: false
                        );

                        try {
                            $generator = new WeekPlanGenerator($days, $prefs);
                            $plan = $generator->generate();

                            if (empty($plan)) {
                                $this->error("  EMPTY PLAN: No exercises found matching these criteria.");
                                continue;
                            }

                            foreach ($plan as $dayKey => $data) {
                                $muscleText = strtoupper(implode(' & ', array_map(fn($m) => str_replace('_', ' ', $m), $data['muscles'])));
                                $exerciseCount = count($data['exercies']);
                                $this->line("  <fg=green>●</> <fg=cyan>{$dayKey}:</> [{$muscleText}] ({$exerciseCount} exercises)");

                                foreach ($data['exercies'] as $ex) {
                                    $this->line("    ― {$ex}");
                                }
                            }
                        } catch (\Exception $e) {
                            $this->error("  FAILED: " . $e->getMessage());
                        }

                        $this->line("--------------------------------------------------");
                    }
                }
            }
            $this->line("\n");
        }

        return 0;
    }
}
