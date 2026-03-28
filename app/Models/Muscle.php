<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Muscle extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'name'];

    public function exercies()
    {
        return $this->belongsToMany(Exercies::class, 'exercie_muscles', 'muscle_id', 'exercie_id')
            ->using(ExercieMuscle::class)
            ->withPivot('type')
            ->withTimestamps();
    }
}
