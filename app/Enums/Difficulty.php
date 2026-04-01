<?php

namespace App\Enums;

enum Difficulty: string
{
    case ALL = "all";
    case BEGINNER = "beginner";
    case INTERMEDIATE = "intermediate";
    case EXPERT = "expert";
}
