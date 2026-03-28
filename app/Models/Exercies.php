<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['id', 'name', 'instructions', 'force', 'level', 'mechanic', 'category_id', 'equipment_id'])]
class Exercies extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
}
