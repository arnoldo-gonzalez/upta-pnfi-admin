<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'number'])]
class Trayecto extends Model
{
    use HasFactory;

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function thematicAxes() { 
        return $this->hasMany(ThematicAxis::class); 
    }
}