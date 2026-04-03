<?php

namespace App\Models;

use App\Enums\ExercieCategory;
use App\Enums\ExerciseFamily;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['id', 'name', 'instructions', 'force', 'level', 'mechanic', 'popularity', 'category', 'exercise_family', 'equipment_id'])]
class Exercies extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'category' => ExercieCategory::class,
        'exercise_family' => ExerciseFamily::class,
    ];

    public function muscles()
    {
        return $this->belongsToMany(Muscle::class, 'exercie_muscles', 'exercie_id', 'muscle_id')
            ->using(ExercieMuscle::class)
            ->withPivot('type')
            ->withTimestamps();
    }

    public function muscleSubdivisions()
    {
        return $this->belongsToMany(MuscleSubdivision::class, 'exercie_subdivisions', 'exercie_id', 'subdivision_id')
            ->using(ExercieSubdivision::class)
            ->withPivot('type')
            ->withTimestamps();
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Helper to always return the mechanic as a string, 
     * even if it's stored as an Enum in the future.
     */
    public function getMechanicStringAttribute(): ?string
    {
        $mechanic = $this->mechanic;
        return is_object($mechanic) ? $mechanic->value : (string)$mechanic;
    }
}
