<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name'])]
class Status extends Model
{
    public function users()
    {
        return $this->hasMany(User::class);
    }
}