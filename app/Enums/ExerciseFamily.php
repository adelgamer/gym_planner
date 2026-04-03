<?php

namespace App\Enums;

enum ExerciseFamily: string
{
  case AB_ROLLOUT = 'ab_rollout';
  case BICEP_CURL_STANDARD = 'bicep_curl_standard';
  case CALF_RAISE = 'calf_raise';
  case CHEST_FLY = 'chest_fly';
  case CORE_MISC = 'core_misc';
  case CRUNCH_VARIATION = 'crunch_variation';
  case DECLINE_BENCH_PRESS = 'decline_bench_press';
  case DIP_VARIATION = 'dip_variation';
  case FLAT_BENCH_PRESS = 'flat_bench_press';
  case FOREARM_ISOLATION = 'forearm_isolation';
  case FRONT_LATERAL_RAISE = 'front_lateral_raise';
  case HAMMER_CURL = 'hammer_curl';
  case HIP_HINGE_HAMSTRING = 'hip_hinge_hamstring';
  case HORIZONTAL_ROW = 'horizontal_row';
  case INCLINE_BENCH_PRESS = 'incline_bench_press';
  case LEG_CURL = 'leg_curl';
  case LEG_EXTENSION = 'leg_extension';
  case LEG_MACHINE_MISC = 'leg_machine_misc';
  case LEG_PRESS = 'leg_press';
  case LEG_RAISE_CORE = 'leg_raise_core';
  case LUNGE_VARIATION = 'lunge_variation';
  case MACHINE_CHEST_PRESS = 'machine_chest_press';
  case OTHER_MISC = 'other_misc';
  case PLANK_VARIATION = 'plank_variation';
  case PREACHER_CURL = 'preacher_curl';
  case PULLOVER_VARIATION = 'pullover_variation';
  case PUSHUP_VARIATION = 'pushup_variation';
  case REAR_DELT_VARIATION = 'rear_delt_variation';
  case RECOVERY_ABDOMINALS = 'recovery_abdominals';
  case RECOVERY_ABDUCTORS = 'recovery_abductors';
  case RECOVERY_ADDUCTORS = 'recovery_adductors';
  case RECOVERY_BICEPS = 'recovery_biceps';
  case RECOVERY_CALVES = 'recovery_calves';
  case RECOVERY_CHEST = 'recovery_chest';
  case RECOVERY_FOREARMS = 'recovery_forearms';
  case RECOVERY_GLUTES = 'recovery_glutes';
  case RECOVERY_HAMSTRINGS = 'recovery_hamstrings';
  case RECOVERY_LATS = 'recovery_lats';
  case RECOVERY_LOWER_BACK = 'recovery_lower_back';
  case RECOVERY_MIDDLE_BACK = 'recovery_middle_back';
  case RECOVERY_NECK = 'recovery_neck';
  case RECOVERY_QUADRICEPS = 'recovery_quadriceps';
  case RECOVERY_SHOULDERS = 'recovery_shoulders';
  case RECOVERY_TRICEPS = 'recovery_triceps';
  case SHRUG_VARIATION = 'shrug_variation';
  case SIDE_LATERAL_RAISE = 'side_lateral_raise';
  case SITUP_VARIATION = 'situp_variation';
  case SQUAT_VARIATION = 'squat_variation';
  case TRICEP_EXTENSION = 'tricep_extension';
  case TRICEP_PUSHDOWN = 'tricep_pushdown';
  case UPRIGHT_ROW = 'upright_row';
  case VERTICAL_OVERHEAD_PRESS = 'vertical_overhead_press';
  case VERTICAL_PULL_BODYWEIGHT = 'vertical_pull_bodyweight';
  case VERTICAL_PULL_MACHINE = 'vertical_pull_machine';
}
