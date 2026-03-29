<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ExercieSubdivision extends Pivot
{
    protected $table = 'exercie_subdivisions';
    protected $fillable = ['exercie_id', 'subdivision_id', 'type'];
}
