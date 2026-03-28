<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ExercieMuscle extends Pivot
{
    protected $table = 'exercie_muscles';
    protected $fillable = ['exercie_id', 'muscle_id', 'type'];
}
