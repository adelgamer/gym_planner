<?php

namespace App\Console\Commands;

use App\Enums\Difficulty;
use App\Enums\EquipmentSelection;
use App\Services\WeekPlanGenerator;
use App\Services\WorkoutPreferences;
use Illuminate\Console\Command;

class TestOneCase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gym:one-case';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test a specific single case: top_p 0.7, difficulty ALL, equipment GYM.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = 3;
        $difficulty = Difficulty::ALL;
        $equipment = EquipmentSelection::GYM;
        $topP = 0.7;

        $this->info("==================================================");
        $this->info("          TESTING SINGLE SPECIFIC CASE");
        $this->info("==================================================");
        
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
                return 1;
            }

            foreach ($plan as $dayKey => $data) {
                // Correct muscle mapping for display
                $muscleNames = array_map(function($m) {
                    return str_replace('_', ' ', is_string($m) ? $m : $m->name);
                }, $data['muscles']);
                
                $muscleText = strtoupper(implode(' & ', $muscleNames));
                $exerciseCount = count($data['exercies']);
                
                $this->line("  <fg=green>●</> <fg=cyan>{$dayKey}:</> [{$muscleText}] ({$exerciseCount} exercises)");

                foreach ($data['exercies'] as $ex) {
                    $this->line("    ― {$ex}");
                }
            }
        } catch (\Exception $e) {
            $this->error("  FAILED: " . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->line("--------------------------------------------------");
        
        return 0;
    }
}
