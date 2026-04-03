<?php

namespace App\Enums;

enum EquipmentSelection: string
{
    case ALL = "all";
    case BODY_WEIGHT = "body weight";
    case GYM = "gym";
    case DUMBBELLS = "dumbbells";

    /**
     * Returns the array of allowed equipment IDs based on the selection.
     */
    public function getAllowedIds(): array
    {
        return match ($this) {
            self::BODY_WEIGHT => [1],
            self::DUMBBELLS => [6],
            self::GYM => [2, 6, 7, 8, 12],
            self::ALL => range(1, 12),
        };
    }
}
