<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentType;
use App\Models\EvaluationStage;
use App\Models\InvestigationLine;
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

        // 3. Líneas de investigación
        InvestigationLine::firstOrCreate(['name' => 'Desarrollo de Software']);
        InvestigationLine::firstOrCreate(['name' => 'Infraestructura y Redes']);
        InvestigationLine::firstOrCreate(['name' => 'Software Educativo']);

        // 4. Estados de la solicitud de tutoría
        TutorRequestStatus::firstOrCreate(['name' => 'sin_solicitud']);
        TutorRequestStatus::firstOrCreate(['name' => 'pendiente']);
        TutorRequestStatus::firstOrCreate(['name' => 'aceptada']);
        TutorRequestStatus::firstOrCreate(['name' => 'rechazada']);
    }
}