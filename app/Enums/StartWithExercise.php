<?php

namespace App\Enums;

enum StartWithExercise: string
{
    case ALL = "all";
    case COMPOUND = "compound";
    case ISOLATION = "isolation";
}
