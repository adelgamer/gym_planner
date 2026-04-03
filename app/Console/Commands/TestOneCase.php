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
        $topP = 0.9;

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
            fullExerciseObject: true // Required for detailed table output
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
                $muscleNames = array_map(function ($m) {
                    return str_replace('_', ' ', is_string($m) ? $m : $m->name);
                }, $data['muscles']);

                $muscleText = strtoupper(implode(' & ', $muscleNames));
                $exerciseCount = count($data['exercies']);

                $this->line("\n  <fg=green>●</> <fg=cyan>{$dayKey}:</> [{$muscleText}] ({$exerciseCount} exercises)");

                $this->line("| Name | Family | Muscle | Subdivisions | Pop | Equip | Mech |");
                $this->line("| :--- | :--- | :--- | :--- | :--- | :--- | :--- |");

                foreach ($data['exercies'] as $ex) {
                    $name = is_array($ex) ? $ex['name'] : $ex->name;
                    $family = is_array($ex) ? ($ex['exercise_family'] ?? '-') : ($ex->exercise_family->value ?? '-');
                    
                    // Fetch muscle name (assuming relation might be loaded or triggered)
                    $muscle = '-';
                    if (is_array($ex) && !empty($ex['muscles'])) {
                        $primary = collect($ex['muscles'])->firstWhere('pivot.type', 'primary');
                        $muscle = $primary['name'] ?? '-';
                    } elseif (!is_array($ex) && $ex->muscles->count() > 0) {
                        $muscle = $ex->muscles->where('pivot.type', 'primary')->first()->name ?? '-';
                    }

                    $pop = is_array($ex) ? $ex['popularity'] : $ex->popularity;
                    
                    $equip = '-';
                    if (is_array($ex) && isset($ex['equipment'])) {
                        $equip = $ex['equipment']['name'];
                    } elseif (!is_array($ex) && $ex->equipment) {
                        $equip = $ex->equipment->name;
                    }

                    $mech = is_array($ex) ? $ex['mechanic'] : $ex->mechanic;
                    
                    $subds = [];
                    if (is_array($ex) && isset($ex['muscle_subdivisions'])) {
                        $subds = array_column($ex['muscle_subdivisions'], 'name');
                    } elseif (!is_array($ex)) {
                        $subds = $ex->muscleSubdivisions->pluck('name')->toArray();
                    }
                    $subdText = implode(', ', $subds);

                    $this->line(sprintf(
                        "| %s | %s | %s | %s | %s | %s | %s |",
                        $name,
                        $family,
                        $muscle,
                        $subdText ?: '-',
                        $pop,
                        $equip,
                        $mech
                    ));
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
