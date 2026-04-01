<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Enums\EquipmentSelection;

class WeekPlanGenerator
{

  /*
  Testing
  use App\Services\WeekPlanGenerator;
  use App\Services\WorkoutPreferences;
  use App\Enums\Difficulty;
  use App\Enums\EquipmentSelection;
  $plan = (new WeekPlanGenerator(3, new WorkoutPreferences(
    fullExerciseObject: false, 
    difficulty: Difficulty::BEGINNER,
    equipmentSelection: EquipmentSelection::GYM
  )))->generate();
  */

  private array $split = [];
  private array $full_plan = [];

  public function __construct(
    public int $daysPerWeek,
    private WorkoutPreferences $prefs = new WorkoutPreferences()
  ) {
    $this->getWeeklySplitPlan();
  }

  private function randomNumberOfExercies()
  {
    return random_int(6, 8);
  }

  private function randomSplit(array $splits)
  {
    $randomKey = array_rand($splits, 1);
    return $splits[$randomKey];
  }

  /**
   * @deprecated use WorkoutPreferences in constructor instead
   */
  public function returnFullExercieseObject(bool $value)
  {
    $this->prefs->fullExerciseObject = $value;
    return $this;
  }

  /**
   * @deprecated use WorkoutPreferences in constructor instead
   */
  public function setEquipmentSelection(EquipmentSelection $selection)
  {
    $this->prefs->equipmentSelection = $selection;
    return $this;
  }

  private function getWeeklySplitPlan(): void
  {
    // validating the number of days of the week
    if ($this->daysPerWeek < 3 || $this->daysPerWeek > 6) {
      throw new \Exception("Days per week must be between 3 to 6");
    }


    $splits = [

      3 => self::randomSplit([

        [ // Push / Pull / Legs
          "day_1" => ["muscles" => ['chest', 'shoulders', 'triceps'], "exerciesesCount" => self::randomNumberOfExercies()], // Push
          "day_2" => ["muscles" => ['lats', 'middle_back', 'biceps'], "exerciesesCount" => self::randomNumberOfExercies()], // Pull
          "day_3" => ["muscles" => ['quadriceps', 'hamstrings', 'glutes', 'abdominals', 'calves'], "exerciesesCount" => self::randomNumberOfExercies()], // Legs
        ],

        // [ // Full Body
        //   "day_1" => ["muscles" => ['chest', 'lats', 'shoulders', 'quadriceps', 'abdominals'], "exerciesesCount" => self::randomNumberOfExercies()],
        //   "day_2" => ["muscles" => ['middle_back', 'triceps', 'biceps', 'hamstrings', 'calves'], "exerciesesCount" => self::randomNumberOfExercies()],
        //   "day_3" => ["muscles" => ['chest', 'lats', 'shoulders', 'glutes', 'abdominals'], "exerciesesCount" => self::randomNumberOfExercies()],
        // ],

        // [ // Upper / Lower / Full
        //   "day_1" => ["muscles" => ['chest', 'lats', 'middle_back', 'shoulders', 'triceps', 'biceps'], "exerciesesCount" => self::randomNumberOfExercies()], // Upper
        //   "day_2" => ["muscles" => ['quadriceps', 'hamstrings', 'glutes', 'calves', 'abductors', 'adductors'], "exerciesesCount" => self::randomNumberOfExercies()], // Lower
        //   "day_3" => ["muscles" => ['chest', 'lats', 'shoulders', 'quadriceps', 'hamstrings', 'abdominals'], "exerciesesCount" => self::randomNumberOfExercies()], // Full
        // ],

      ]),

      4 => self::randomSplit([

        [ // Upper / Lower
          "day_1" => ["muscles" => ['chest', 'lats', 'shoulders', 'triceps'], "exerciesesCount" => self::randomNumberOfExercies()], // Upper A
          "day_2" => ["muscles" => ['quadriceps', 'hamstrings', 'glutes', 'calves'], "exerciesesCount" => self::randomNumberOfExercies()], // Lower A
          "day_3" => ["muscles" => ['middle_back', 'biceps', 'traps', 'forearms'], "exerciesesCount" => self::randomNumberOfExercies()], // Upper B
          "day_4" => ["muscles" => ['quadriceps', 'hamstrings', 'glutes', 'abdominals'], "exerciesesCount" => self::randomNumberOfExercies()], // Lower B
        ],

        [ // Push / Pull Split
          "day_1" => ["muscles" => ['chest', 'shoulders', 'triceps'], "exerciesesCount" => self::randomNumberOfExercies()], // Push A
          "day_2" => ["muscles" => ['lats', 'middle_back', 'biceps', 'traps'], "exerciesesCount" => self::randomNumberOfExercies()], // Pull A
          "day_3" => ["muscles" => ['quadriceps', 'hamstrings', 'glutes', 'calves'], "exerciesesCount" => self::randomNumberOfExercies()], // Legs
          "day_4" => ["muscles" => ['abdominals', 'forearms', 'neck'], "exerciesesCount" => self::randomNumberOfExercies()], // Core / small muscles
        ],

        [ // Modified Bro Split
          "day_1" => ["muscles" => ['chest', 'triceps'], "exerciesesCount" => self::randomNumberOfExercies()],
          "day_2" => ["muscles" => ['lats', 'middle_back', 'biceps'], "exerciesesCount" => self::randomNumberOfExercies()],
          "day_3" => ["muscles" => ['shoulders', 'traps', 'abdominals'], "exerciesesCount" => self::randomNumberOfExercies()],
          "day_4" => ["muscles" => ['quadriceps', 'hamstrings', 'glutes', 'calves'], "exerciesesCount" => self::randomNumberOfExercies()],
        ],

        [ // PPL + Full Body
          "day_1" => ["muscles" => ['chest', 'shoulders', 'triceps'], "exerciesesCount" => self::randomNumberOfExercies()], // Push
          "day_2" => ["muscles" => ['lats', 'middle_back', 'biceps'], "exerciesesCount" => self::randomNumberOfExercies()], // Pull
          "day_3" => ["muscles" => ['quadriceps', 'hamstrings', 'glutes', 'calves'], "exerciesesCount" => self::randomNumberOfExercies()], // Legs
          "day_4" => ["muscles" => ['chest', 'lats', 'shoulders', 'abdominals'], "exerciesesCount" => self::randomNumberOfExercies()], // Full
        ],

      ]),

      5 => self::randomSplit([

        [ // Standard Bro Split
          "day_1" => ["muscles" => ['chest'], "exerciesesCount" => self::randomNumberOfExercies()],
          "day_2" => ["muscles" => ['lats', 'middle_back', 'traps'], "exerciesesCount" => self::randomNumberOfExercies()],
          "day_3" => ["muscles" => ['shoulders', 'abdominals'], "exerciesesCount" => self::randomNumberOfExercies()],
          "day_4" => ["muscles" => ['quadriceps', 'hamstrings', 'glutes'], "exerciesesCount" => self::randomNumberOfExercies()],
          "day_5" => ["muscles" => ['biceps', 'triceps', 'calves'], "exerciesesCount" => self::randomNumberOfExercies()],
        ],

        // [ // Upper Lower + PPL
        //   "day_1" => ["muscles" => ['chest', 'lats', 'shoulders'], "exerciesesCount" => self::randomNumberOfExercies()], // Upper
        //   "day_2" => ["muscles" => ['quadriceps', 'hamstrings', 'glutes', 'calves'], "exerciesesCount" => self::randomNumberOfExercies()], // Lower
        //   "day_3" => ["muscles" => ['chest', 'shoulders', 'triceps'], "exerciesesCount" => self::randomNumberOfExercies()], // Push
        //   "day_4" => ["muscles" => ['lats', 'middle_back', 'biceps', 'traps'], "exerciesesCount" => self::randomNumberOfExercies()], // Pull
        //   "day_5" => ["muscles" => ['quadriceps', 'hamstrings', 'glutes', 'abdominals'], "exerciesesCount" => self::randomNumberOfExercies()], // Legs
        // ],

        // [ // PHAT Style
        //   "day_1" => ["muscles" => ['chest', 'shoulders', 'triceps'], "exerciesesCount" => self::randomNumberOfExercies()], // Upper Power
        //   "day_2" => ["muscles" => ['quadriceps', 'hamstrings', 'glutes', 'calves'], "exerciesesCount" => self::randomNumberOfExercies()], // Lower Power
        //   "day_3" => ["muscles" => ['lats', 'middle_back', 'biceps', 'traps'], "exerciesesCount" => self::randomNumberOfExercies()], // Back Hypertrophy
        //   "day_4" => ["muscles" => ['chest', 'shoulders', 'triceps'], "exerciesesCount" => self::randomNumberOfExercies()], // Chest Hypertrophy
        //   "day_5" => ["muscles" => ['quadriceps', 'hamstrings', 'glutes', 'abdominals'], "exerciesesCount" => self::randomNumberOfExercies()], // Legs Hypertrophy
        // ],

      ]),

      6 => self::randomSplit([

        [ // PPL x2
          "day_1" => ["muscles" => ['chest', 'shoulders', 'triceps'], "exerciesesCount" => self::randomNumberOfExercies()], // Push A
          "day_2" => ["muscles" => ['lats', 'middle_back', 'biceps'], "exerciesesCount" => self::randomNumberOfExercies()], // Pull A
          "day_3" => ["muscles" => ['quadriceps', 'hamstrings', 'glutes', 'calves'], "exerciesesCount" => self::randomNumberOfExercies()], // Legs A
          "day_4" => ["muscles" => ['chest', 'shoulders', 'triceps'], "exerciesesCount" => self::randomNumberOfExercies()], // Push B
          "day_5" => ["muscles" => ['lats', 'middle_back', 'biceps', 'traps'], "exerciesesCount" => self::randomNumberOfExercies()], // Pull B
          "day_6" => ["muscles" => ['quadriceps', 'hamstrings', 'glutes', 'calves'], "exerciesesCount" => self::randomNumberOfExercies()], // Legs B
        ],

        [ // Arnold Split
          "day_1" => ["muscles" => ['chest', 'middle_back'], "exerciesesCount" => self::randomNumberOfExercies()],
          "day_2" => ["muscles" => ['shoulders', 'biceps', 'triceps'], "exerciesesCount" => self::randomNumberOfExercies()],
          "day_3" => ["muscles" => ['quadriceps', 'hamstrings', 'glutes', 'calves'], "exerciesesCount" => self::randomNumberOfExercies()],
          "day_4" => ["muscles" => ['chest', 'lats'], "exerciesesCount" => self::randomNumberOfExercies()],
          "day_5" => ["muscles" => ['shoulders', 'biceps', 'triceps', 'traps'], "exerciesesCount" => self::randomNumberOfExercies()],
          "day_6" => ["muscles" => ['quadriceps', 'hamstrings', 'glutes', 'calves', 'abdominals'], "exerciesesCount" => self::randomNumberOfExercies()],
        ],

        [ // Upper Lower x3
          "day_1" => ["muscles" => ['chest', 'lats', 'shoulders', 'triceps'], "exerciesesCount" => self::randomNumberOfExercies()],
          "day_2" => ["muscles" => ['quadriceps', 'hamstrings', 'glutes', 'calves'], "exerciesesCount" => self::randomNumberOfExercies()],
          "day_3" => ["muscles" => ['middle_back', 'biceps', 'traps', 'forearms'], "exerciesesCount" => self::randomNumberOfExercies()],
          "day_4" => ["muscles" => ['quadriceps', 'hamstrings', 'glutes', 'abdominals'], "exerciesesCount" => self::randomNumberOfExercies()],
          "day_5" => ["muscles" => ['chest', 'shoulders', 'triceps'], "exerciesesCount" => self::randomNumberOfExercies()],
          "day_6" => ["muscles" => ['quadriceps', 'hamstrings', 'glutes', 'calves'], "exerciesesCount" => self::randomNumberOfExercies()],
        ],

      ]),

    ];

    // Return the specific split, or an empty array if the number of days is invalid
    Log::debug('Shape', $splits);
    $this->split = $splits[$this->daysPerWeek] ?? [];
  }

  public function generate()
  {
    foreach ($this->split as $key => $day) {

      $day_plan = (new DayWorkoutGeneratorService(
        $day['muscles'],
        $day['exerciesesCount'],
        $this->prefs
      ));

      $day_plan = $day_plan->generate();

      if (!$this->prefs->fullExerciseObject) {

        $this->full_plan[$key] = [
          "muscles" => $day['muscles'],
          "exercies" => $day_plan->pluck('name')->toArray()
        ];
      } else {

        $this->full_plan[$key] = [
          "muscles" => $day['muscles'],
          "exercies" => $day_plan->toArray()
        ];
      }
    }

    return $this->full_plan;
  }
}
