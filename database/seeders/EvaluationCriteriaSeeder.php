<?php

namespace Database\Seeders;

use App\Models\CriteriaType;
use Illuminate\Database\Seeder;
use App\Models\EvaluationCriteria;
use App\Models\ThematicAxis;

class EvaluationCriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $axes = ThematicAxis::all();
        
        // Buscamos los tipos que creamos en CriteriaTypeSeeder
        $writtenType = CriteriaType::where('name', 'Informe Escrito')->first();
        $technicalType = CriteriaType::where('name', 'Desarrollo Técnico')->first();

        // Validación de seguridad por si olvidaron correr el otro seeder
        if (!$writtenType || !$technicalType) {
            $this->command->error('Debes ejecutar CriteriaTypeSeeder primero.');
            return;
        }

        foreach ($axes as $axis) {
            
            // ==========================================
            // CRITERIOS ESCRITOS (La suma interna es 100%)
            // ==========================================
            EvaluationCriteria::firstOrCreate(
                ['thematic_axis_id' => $axis->id, 'criteria_type_id' => $writtenType->id, 'name' => 'Redacción y Ortografía'], 
                ['weight_percentage' => 20]
            );
            EvaluationCriteria::firstOrCreate(
                ['thematic_axis_id' => $axis->id, 'criteria_type_id' => $writtenType->id, 'name' => 'Estructura del Informe'], 
                ['weight_percentage' => 30]
            );
            EvaluationCriteria::firstOrCreate(
                ['thematic_axis_id' => $axis->id, 'criteria_type_id' => $writtenType->id, 'name' => 'Justificación y Alcance'], 
                ['weight_percentage' => 50]
            );

            // ==========================================
            // CRITERIOS TÉCNICOS (La suma interna es 100%)
            // ==========================================
            EvaluationCriteria::firstOrCreate(
                ['thematic_axis_id' => $axis->id, 'criteria_type_id' => $technicalType->id, 'name' => 'Base de Datos / Procedimientos'], 
                ['weight_percentage' => 40]
            );
            EvaluationCriteria::firstOrCreate(
                ['thematic_axis_id' => $axis->id, 'criteria_type_id' => $technicalType->id, 'name' => 'Módulo de Seguridad'], 
                ['weight_percentage' => 30]
            );
            EvaluationCriteria::firstOrCreate(
                ['thematic_axis_id' => $axis->id, 'criteria_type_id' => $technicalType->id, 'name' => 'Generación de Reportes'], 
                ['weight_percentage' => 30]
            );
        }
    }
}