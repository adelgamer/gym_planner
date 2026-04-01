<?php

namespace App\Enums;

enum ExercieCategory: string
{
    case ALL = "all";
    case STRENGTH = "strength";
    case STRETCHING = "stretching";
    case PLYOMETRICS = "plyometrics";
    case STRONGMAN = "strongman";
    case POWERLIFTING = "powerlifting";
    case CARDIO = "cardio";
    case OLYMPIC_WEIGHTLIFTING = "olympic weightlifting";
}
