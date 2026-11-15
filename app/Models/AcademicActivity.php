<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['academic_term_id', 'name', 'type', 'start_date', 'end_date'])]
class AcademicActivity extends Model
{
    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];

    public function term() { 
        return $this->belongsTo(AcademicTerm::class); 
    }

    // Helper para validar si una actividad está abierta
    public static function isOpen($type)
    {
        $activity = self::where('type', $type)->first();
        if (!$activity) return false; // Si no se ha configurado, está cerrada por defecto
        
        return now()->between($activity->start_date, $activity->end_date);
    }
}
