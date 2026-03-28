<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['id', 'name', 'instructions', 'force', 'level', 'mechanic', 'popularity', 'category_id', 'equipment_id'])]
class Exercies extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    public function muscles()
    {
        return $this->belongsToMany(Muscle::class, 'exercie_muscles', 'exercie_id', 'muscle_id')
            ->using(ExercieMuscle::class)
            ->withPivot('type')
            ->withTimestamps();
    }
}
