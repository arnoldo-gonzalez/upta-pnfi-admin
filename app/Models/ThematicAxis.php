<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'trayecto_id'])]
class ThematicAxis extends Model
{
    public function trayecto() { 
        return $this->belongsTo(Trayecto::class); 
    }

    public function investigationLines() { 
        return $this->hasMany(InvestigationLine::class); 
    }
}
