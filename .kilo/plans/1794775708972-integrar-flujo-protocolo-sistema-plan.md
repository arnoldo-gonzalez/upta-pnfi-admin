## Plan: Integrar generación docx con flujo del sistema

### Objetivo
Reemplazar el flujo actual de subida de PDF por un flujo basado en formulario (variables del protocolo), donde las respuestas del estudiante se guardan en BD y se usan para generar el docx al descargar, y donde los docentes pueden dar correcciones por campo (variable).

### Estado actual
- **Estudiante**: sube PDF → zip → `ProjectDocument` → estado PENDING
- **Coordinador** (`coordinator/protocols`): lista proyectos con estados
- **Docente** (`evaluations/teacher/review`): descarga PDF, aprueba/rechaza con observaciones generales
- **Plantilla**: `AdminProtocolTemplateController` gestiona .docx + schema de variables
- **Preview MVP**: `ProtocolPreviewController::create()`/`preview()` genera docx desde formulario

### Modelo de datos

#### Migración 1: `protocol_template_id` en `project_documents`
- Agregar `protocol_template_id` (nullable FK a `protocol_templates`)
- Para vincular cada envío con la versión de plantilla usada

#### Migración 2: `project_protocol_answers`
- `id`, `project_document_id` (FK), `placeholder_key` (string), `value` (text)
- Índice único: `(project_document_id, placeholder_key)`
- Una fila por variable del protocolo por envío

#### Migración 3: `project_protocol_field_corrections`
- `id`, `project_document_id` (FK), `placeholder_key` (string), `correction` (text), `evaluator_id` (FK users)
- Índice único: `(project_document_id, placeholder_key)`
- Una fila por variable por documento (última corrección del evaluador gana)

### Modelos

- `ProjectProtocolAnswer` — `projectDocument()`, casts `value` a string
- `ProjectProtocolFieldCorrection` — `projectDocument()`, `evaluator()`
- `ProjectDocument` — agregar `protocolTemplate()`, `answers()`, `fieldCorrections()`
- `ProjectEvaluation` — agregar `fieldCorrections()` vía `projectDocument`

### Rutas

```
// Estudiante (rol: estudiante)
GET  /projects/{project}/protocol/fill          → ProtocolPreviewController::fillProject
POST /projects/{project}/protocol/preview       → ProtocolPreviewController::previewProject
POST /projects/{project}/protocol/submit        → ProjectController::submitProtocol (reemplaza PDF)

// Docente/asesor/coordinador (rol: admin|coordinador|docente)
GET  /projects/{project}/protocol/download      → ProjectDocumentController::downloadRegenerated
POST /evaluations/{evaluation}/field-correction  → EvaluationController::storeFieldCorrection

// Coordinador (rol: admin|coordinador)
GET  /coordinator/protocols                     → ProjectController::protocols (ya existe, + botón editar)
GET  /coordinator/protocols/edit                → AdminProtocolTemplateController::show (reenvío)
```

### Controladores

#### `ProtocolPreviewController`
1. `fillProject(Project $project)` — GET
   - Carga plantilla activa + respuestas guardadas si existen
   - Pasa variables con valores `old()` o de BD a la vista `protocol/form`

2. `previewProject(Project $project, Request $request)` — POST
   - Valida campos (igual que `preview()`)
   - Genera docx en memoria con `replaceSplitPlaceholders()`
   - Retorna download del docx previsualizado

#### `ProjectController::submitProtocol(Project $project, Request $request)` — POST (REEMPLAZA PDF)
- Valida campos del schema de la plantilla activa
- Genera docx desde plantilla + respuestas (reutiliza lógica de `replaceSplitPlaceholders()`)
- Guarda docx en `storage/app/private/protocols/{uuid}.docx`
- Crea `ProjectDocument` con `protocol_template_id`, `file_path`, `submission_number`
- Guarda cada respuesta en `project_protocol_answers`
- Cambia estado a PENDING
- Crea evaluaciones (misma lógica actual de `createNewEvaluationsForSubmission`)

#### `EvaluationController`
1. `reviewForm(ProjectEvaluation $evaluation)` — GET
   - Carga `projectDocument`, `answers`, `fieldCorrections`
   - Pasa a la vista para mostrar el formulario con correcciones

2. `storeReview(ProjectEvaluation $evaluation, Request $request)` — POST
   - Lógica actual de aprobación/rechazo
   - Además: procesa `field_corrections` array del request
   - Guarda/actualiza en `project_protocol_field_corrections`

3. `storeFieldCorrection(ProjectEvaluation $evaluation, Request $request)` — POST
   - Valida `placeholder_key` y `correction`
   - Upsert en `project_protocol_field_corrections`

#### `ProjectDocumentController::downloadRegenerated(Project $project)` — GET
- Carga el último `ProjectDocument` del proyecto
- Carga plantilla (`protocol_template_id`) + respuestas (`project_protocol_answers`)
- Genera docx con `TemplateProcessor` + `replaceSplitPlaceholders()`
- Retorna download

#### `AdminProtocolTemplateController`
- Ya existe, sin cambios (accesible desde `admin.protocolo.plantilla`)

### Vistas

#### `projects/index.blade.php` — Reemplazar formulario PDF (líneas 222-238)
- Si estado es DRAFT o NEEDS_CORRECTIONS:
  - Mostrar formulario inline con campos del schema de plantilla
  - Pre-llenar con respuestas guardadas (`old()` fallback BD)
  - Botones: "Previsualizar" (POST a `previewProject`) y "Subir protocolo" (POST a `submitProtocol`)
- Si estado es PENDING:
  - Mostrar enlace "Descargar protocolo" (GET a `downloadRegenerated`)
  - Mostrar correcciones por campo si existe `fieldCorrections` (rechazo)

#### `evaluations/teacher/review.blade.php` — Agregar:
- Botón "Descargar protocolo" (GET `downloadRegenerated`) en el bloque del documento
- Sección de correcciones por campo: para cada placeholder con respuesta del estudiante:
  - Mostrar valor del estudiante
  - Input/textarea para corrección del evaluador (nombre: `field_corrections[{key}]`)
  - Los inputs van dentro del form de evaluación (mismo POST)

#### `coordinator/protocols.blade.php` — Agregar columna de acciones:
- Botón "Editar protocolo" → link a `admin.protocolo.plantilla`
- Botón "Ver proyecto" (si existe o se agrega)

#### `protocol/form.blade.php` — Reutilizar como base
- Modificar para aceptar valores pre-llenados (ya soporta `old()`)
- Agregar `data-project-id` al form para distinguir modo standalone vs project

### Flujo completo

1. **Estudiante llena formulario** → `GET /projects/{project}/protocol/fill` → vista `protocol/form` con campos pre-llenados
2. **Estudiante previsualiza** → `POST /projects/{project}/protocol/preview` → descarga docx en memoria
3. **Estudiante envía** → `POST /projects/{project}/protocol/submit` → guarda respuestas + genera docx + cambia estado
4. **Docente descarga** → `GET /projects/{project}/protocol/download` → docx regenerado con respuestas
5. **Docente corrige** → `POST /evaluations/{evaluation}/store` → aprueba/rechaza + guarda correcciones por campo
6. **Estudiante ve correcciones** → `projects/index` muestra campo + corrección del docente

### Riesgos y consideraciones
- La plantilla puede cambiar entre envíos → `protocol_template_id` en `project_document` lo maneja
- Las respuestas deben persistir al redirigir por errores de validación → usar `old()`
- El schema de la plantilla puede cambiar keys → normalizar keys igual que `AdminProtocolTemplateController`
- El docx generado puede ser grande → usar Storage en lugar de memoria para envío final
- Compatibilidad con evaluadores que no usan correcciones por campo → `field_corrections` es opcional
- Los `observations` generales existentes se mantienen como respaldo

### Archivos a modificar
- `app/Http/Controllers/ProjectController.php` — `submitProtocol()`, `protocols()`
- `app/Http/Controllers/ProtocolPreviewController.php` — `fillProject()`, `previewProject()`
- `app/Http/Controllers/EvaluationController.php` — `reviewForm()`, `storeReview()`, `storeFieldCorrection()`
- `app/Http/Controllers/ProjectDocumentController.php` — `downloadRegenerated()`
- `app/Models/ProjectDocument.php` — relaciones + fillable
- `routes/web.php` — nuevas rutas
- `resources/views/projects/index.blade.php` — formulario inline
- `resources/views/evaluations/teacher/review.blade.php` — descarga + correcciones por campo
- `resources/views/coordinator/protocols.blade.php` — botón editar protocolo
- `resources/views/protocol/form.blade.php` — soporte para valores pre-llenados

### Archivos nuevos
- `app/Models/ProjectProtocolAnswer.php`
- `app/Models/ProjectProtocolFieldCorrection.php`
- `database/migrations/2026_11_17_000001_add_protocol_template_id_to_project_documents.php`
- `database/migrations/2026_11_17_000002_create_project_protocol_answers.php`
- `database/migrations/2026_11_17_000003_create_project_protocol_field_corrections.php`

### Validación
- [ ] `php artisan migrate` corre sin errores
- [ ] `php -l` pasa en todos los controladores y modelos modificados
- [ ] Estudiante puede llenar, previsualizar y enviar protocolo
- [ ] Docente puede descargar docx con respuestas del estudiante
- [ ] Docente puede dar correcciones por campo
- [ ] Estudiante ve correcciones por campo cuando el proyecto es NEEDS_CORRECTIONS
- [ ] Coordinador puede acceder a edición de plantilla desde protocols report