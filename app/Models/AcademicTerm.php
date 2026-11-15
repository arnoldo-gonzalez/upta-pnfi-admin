<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'period', 'start_date', 'end_date'])]
class AcademicTerm extends Model
{
    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];

    public function activities() { 
        return $this->hasMany(AcademicActivity::class); 
    }
    
    // Saber si estamos en este trimestre
    public static function current()
    {
        return self::where('start_date', '<=', now())->where('end_date', '>=', now())->first();
    }
}
