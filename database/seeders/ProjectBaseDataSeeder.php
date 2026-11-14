<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentType;
use App\Models\EvaluationStage;
use App\Models\InvestigationLine;
use App\Models\ThematicAxis;
use App\Models\Trayecto;
use App\Models\TutorRequestStatus;

class ProjectBaseDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tipos de documentos
        DocumentType::firstOrCreate(['name' => 'Protocolo']);
        DocumentType::firstOrCreate(['name' => 'Carta de Aceptación']);
        DocumentType::firstOrCreate(['name' => 'Minuta de Tutoría']);
        DocumentType::firstOrCreate(['name' => 'Informe Final']);

        // 2. Etapas de evaluación
        EvaluationStage::firstOrCreate(['name' => 'Protocolo']);
        EvaluationStage::firstOrCreate(['name' => 'Predefensa']);
        EvaluationStage::firstOrCreate(['name' => 'Defensa Final']);

        $trayectos = Trayecto::all();

        foreach ($trayectos as $trayecto) {
            if ($trayecto->number == 1) {
                $eje = ThematicAxis::firstOrCreate(['name' => 'Soporte Técnico y Atención al Usuario', 'trayecto_id' => $trayecto->id]);
                InvestigationLine::firstOrCreate(['name' => 'Mantenimiento de Equipos', 'thematic_axis_id' => $eje->id]);
                InvestigationLine::firstOrCreate(['name' => 'Reparación de Hardware', 'thematic_axis_id' => $eje->id]);
            } elseif ($trayecto->number == 2) {
                $eje = ThematicAxis::firstOrCreate(['name' => 'Desarrollo de Software y Redes', 'trayecto_id' => $trayecto->id]);
                InvestigationLine::firstOrCreate(['name' => 'Desarrollo Web', 'thematic_axis_id' => $eje->id]);
                InvestigationLine::firstOrCreate(['name' => 'Redes', 'thematic_axis_id' => $eje->id]);
            } elseif ($trayecto->number == 3) {
                $eje = ThematicAxis::firstOrCreate(['name' => 'Desarrollo de Soluciones Informáticas', 'trayecto_id' => $trayecto->id]);
                InvestigationLine::firstOrCreate(['name' => 'Software a Medida', 'thematic_axis_id' => $eje->id]);
                InvestigationLine::firstOrCreate(['name' => 'Aplicaciones Móviles', 'thematic_axis_id' => $eje->id]);
            } elseif ($trayecto->number == 4) {
                $eje = ThematicAxis::firstOrCreate(['name' => 'Gestión e Implantación del Software', 'trayecto_id' => $trayecto->id]);
                InvestigationLine::firstOrCreate(['name' => 'Implantación de Sistemas', 'thematic_axis_id' => $eje->id]);
                InvestigationLine::firstOrCreate(['name' => 'Auditoría de Software', 'thematic_axis_id' => $eje->id]);
            }
        }

        // 4. Estados de la solicitud de tutoría
        TutorRequestStatus::firstOrCreate(['id' => 1, 'name' => 'sin_solicitud']);
        TutorRequestStatus::firstOrCreate(['id' => 2, 'name' => 'pendiente']);
        TutorRequestStatus::firstOrCreate(['id' => 3, 'name' => 'aceptada']);
        TutorRequestStatus::firstOrCreate(['id' => 4, 'name' => 'rechazada']);
    }
}