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

    // Helper para validar si una actividad está abierta.
    // Se busca la ventana vigente (varias pueden existir, una por trimestre) y
    // se incluye el día completo de cierre.
    public static function isOpen($type)
    {
        $activity = self::query()
            ->where('type', $type)
            ->whereDate('start_date', '<=', now()->toDateString())
            ->whereDate('end_date', '>=', now()->toDateString())
            ->latest('start_date')
            ->first();

        if (! $activity) {
            return false; // Sin ventana vigente, cerrada por defecto.
        }

        return now()->betweenIncluded(
            $activity->start_date->copy()->startOfDay(),
            $activity->end_date->copy()->endOfDay()
        );
    }
}
