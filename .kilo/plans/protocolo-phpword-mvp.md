# Plan MVP: Editor de Protocolos con PHPWord (Fase 1)

## Objetivo

Reemplazar el flujo actual de "subir PDF estático" por un flujo basado en plantilla `.docx` editable por el coordinador y rellenable por el estudiante, limitando el alcance de esta fase a:

- Coordinador sube `.docx` con placeholders `{{VARIABLES}}`.
- Sistema detecta y normaliza las variables, genera schema editable.
- Coordinador configura schema (label, tipo, required, maxChars, opciones).
- Estudiante rellena formulario dinámico generado desde el schema.
- Botón "Previsualizar" → descarga un `.docx` ya rellenado con los datos.

Fuera de alcance esta fase: LibreOffice, conversión a PDF, colas/Horizon, polling, tabla `submissions`, integración con `ProjectDocument`/`EvaluationStage`. Todo eso es Fase 2.

---

## Supuestos validados contra el código real

- El proyecto usa **Laravel 13**, **PHP 8.3**, **PostgreSQL**, **Spatie Permission** con roles `admin`, `coordinador`, `docente`, `estudiante`.
- Las vistas usan **Blade** y layout `resources/views/layouts/app.blade.php`.
- Existe el helper `auth()->user()->hasRole(...)` para control de acceso.
- Tabla `document_types` ya existe (usada por `ProjectDocument`). No se toca en esta fase.
- `.env` usa `FILESYSTEM_DISK=local`.
- La plantilla `.docx` debe escribirse con placeholders `{{VAR}}` de un tirón en Word para que `TemplateProcessor::getVariables()` los detecte bien (si Word parte el texto en runs, puede fallar).

---

## Decisiones de diseño

1. **Tabla nueva:** `protocol_templates` (única tabla nueva de esta fase).
2. **Sin tabla `submissions` ni `project_documents` todavía.** El flujo final de guardar el documento rellenado como documento del proyecto se deja para Fase 2.
3. **Almacenamiento:** `disk=local`, carpeta `storage/app/private/protocol_templates/{uuid}/plantilla.docx`. El nombre del archivo original nunca se expone.
4. **Nombre del archivo descargado:** `protocolo_previsualizado.docx` (temporal, eliminado tras envío).
5. **Escape de valores:** `setValue($key, (string) $value, true)` para evitar inyección de placeholders.
6. **Vistas:** Reutilizar `x-app-layout` (via `resources/views/layouts/app.blade.php`) si existe; si no, layout genérico Blade.
7. **Acceso:**
   - Subir/configurar plantilla: `admin`, `coordinador`.
   - Ver formulario + previsualizar: `estudiante` (y `admin`/`coordinador` para supervisión opcional).
8. **Validación backend basada en schema:** nunca confiar solo en el frontend.

---

## Tareas de implementación (ordenadas)

### T1. Migración: tabla `protocol_templates`

Crear migration `create_protocol_templates_table`:

```php
Schema::create('protocol_templates', function (Blueprint $table) {
    $table->id();
    $table->string('nombre');
    $table->string('docx_path');      // ruta relativa en storage (private)
    $table->longText('schema_json');  // array de placeholders con su config
    $table->boolean('activo')->default(true);
    $table->foreignId('created_by')->nullable()->constrained('users');
    $table->timestamps();
});
```

### T2. Modelo `ProtocolTemplate`

`app/Models/ProtocolTemplate.php`:

- `$guarded = []`
- `$casts = ['schema_json' => 'array']`
- Accessor opcional `schema()` que devuelve `schema_json` normalizado.
- Método estático para obtener la plantilla activa: `ProtocolTemplate::where('activo', true)->first()`.

### T3. Controlador `AdminProtocolTemplateController`

`app/Http/Controllers/AdminProtocolTemplateController.php`:

- `show()` — lista plantilla activa con sus variables detectadas y schema actual.
- `store(Request $request)` — subir `.docx`, parsear con `TemplateProcessor::getVariables()`, normalizar claves (mayúsculas, `_`, único), generar schema por defecto, guardar en `protocol_templates`.
- `updateSchema(Request $request, ProtocolTemplate $template)` — PATCH. Recibe array de placeholders editados (key, label, type, required, maxChars, options). Valida y sobrescribe `schema_json`.

Validación de subida: `required|file|mimes:docx|max:10240`. Nombre guardado con `Str::uuid()`.

### T4. Controlador `ProtocolPreviewController`

`app/Http/Controllers/ProtocolPreviewController.php`:

- `create()` — GET formulario del estudiante. Obtiene plantilla activa, lee schema, pasa a vista.
- `preview(Request $request)` — POST. Recibe datos del formulario, valida contra schema (ver sección Validaciones), rellena el `.docx` con `TemplateProcessor`, guarda temporal, devuelve `response()->download(...)->deleteFileAfterSend(true)`.

### T5. Rutas (`routes/web.php`)

Dentro de `Route::middleware(['auth'])->group(...)`:

```php
// Admin / Coordinador: gestión de plantilla
Route::get('/admin/protocolo/plantilla', [AdminProtocolTemplateController::class, 'show'])
    ->name('admin.protocolo.plantilla')
    ->middleware(['can:manage-protocol-templates']); // o role admin|coordinador
Route::post('/admin/protocolo/plantilla', [AdminProtocolTemplateController::class, 'store'])
    ->name('admin.protocolo.plantilla.store');
Route::patch('/admin/protocolo/plantilla/schema', [AdminProtocolTemplateController::class, 'updateSchema'])
    ->name('admin.protocolo.plantilla.schema');

// Estudiante: formulario y previsualización
Route::get('/protocolo/llenar', [ProtocolPreviewController::class, 'create'])
    ->name('protocolo.llenar')
    ->middleware(['role:estudiante']);
Route::post('/protocolo/llenar/preview', [ProtocolPreviewController::class, 'preview'])
    ->name('protocolo.llenar.preview')
    ->middleware(['role:estudiante']);
```

Nota: si no existe el middleware `can:manage-protocol-templates`, usar `role:admin|coordinador` directamente en la ruta.

### T6. Vistas Blade

#### T6a. `resources/views/admin/protocol-template.blade.php`

- Extiende `layouts.app`.
- Formulario de subida `.docx` (file, accept=".docx", enctype multipart).
- Si existe plantilla activa: tabla editable con columnas:
  - Key (readonly, font-mono)
  - Label visible (input text, editable)
  - Tipo (select: text, textarea, email, tel, cedula, number, date, select)
  - Requerido (checkbox)
  - Max chars (input number)
  - Opciones (textarea, JSON o separadas por coma, solo si type=select)
- Botón "Guardar configuración" → PATCH `admin.protocolo.plantilla.schema`.
- Usar Alpine.js para filas dinámicas si hay varias placeholders (opcional pero recomendado).

#### T6b. `resources/views/protocol/form.blade.php`

- Extiende `layouts.app`.
- Formulario generado dinámicamente desde el schema:
  - Label + `*` si es requerido.
  - Input según tipo:
    - `text`, `email`, `tel`, `number`, `date`, `select`.
    - `textarea` para tipo `textarea`.
  - `maxlength` desde `maxChars`.
  - `required` HTML si es required.
  - Select con opciones si type=`select`.
- Botón "Previsualizar protocolo" → POST `protocolo.llenar.preview`.
- Mensajes de error de validación (Laravel `$errors`).

### T7. Validación de datos (ley central)

En `ProtocolPreviewController@preview`, construir reglas desde el schema:

```php
$rules = [];
foreach ($template->schema['placeholders'] as $campo) {
    $rules[$campo['key']] = [];

    if (!empty($campo['required'])) {
        $rules[$campo['key']][] = 'required';
    } else {
        $rules[$campo['key']][] = 'nullable';
    }

    $rules[$campo['key']][] = match ($campo['type']) {
        'email'  => 'email',
        'number' => 'numeric',
        'date'   => 'date',
        'tel'    => 'regex:/^\+?[\d\s\-().]{7,20}$/',
        'cedula' => 'regex:/^[VE]-?\d{1,2}\.?\d{3}\.?\d{3}$/i',
        'select' => 'in:' . implode(',', $campo['options'] ?? []),
        default  => 'string',
    };

    if (!empty($campo['maxChars']) && in_array($campo['type'], ['text','textarea','email'])) {
        $rules[$campo['key']][] = 'max:' . $campo['maxChars'];
    }
}
```

Esto garantiza que:
- No se aceptan inputs requeridos vacíos.
- Tipos validados en servidor.
- Valores escapados al insertar en el `.docx` (evita inyección de placeholders).

### T8. Rellenado del `.docx` (PHPWord)

```php
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;

$src = Storage::disk('local')->path($template->docx_path);
$tp = new TemplateProcessor($src);

foreach ($datosValidados as $key => $value) {
    $tp->setValue($key, (string) $value, true); // escape = true
}

$tmp = sys_get_temp_dir() . '/' . Str::uuid() . '.docx';
$tp->saveAs($tmp);

return response()->download($tmp, 'protocolo_previsualizado.docx')
    ->deleteFileAfterSend(true);
```

### T9. Dependencias Composer

Agregar a `composer.json` (o instalar):
```json
"require": {
    "phpoffice/phpword": "^2.0"
}
```
Ejecutar `composer require phpoffice/phpword` (o `composer install` si ya está declarado).

---

## Edge cases y riesgos

1. **Word rompe placeholders en runs múltiples:** si el usuario edita `{{VAR}}` carácter por carácter en Word, `getVariables()` puede no detectarlo. Mitigación: documentar que se debe escribir el placeholder de un tirón. Opcional: fallback con regex sobre XML interno del `.docx`.
2. **Placeholder duplicado después de normalización:** aplicar `array_unique` sobre claves normalizadas antes de generar el schema.
3. **Archivo `.docx` corrupto o no válido:** `TemplateProcessor` lanzará excepción; capturar y devolver error legible.
4. **Campo `maxChars` vacío:** solo aplicar `max` si existe y el tipo lo permite.
5. **Sin plantilla activa:** el formulario del estudiante debe mostrar mensaje "No hay plantilla de protocolo configurada" en lugar de romper.
6. **Espacio en disco temporal:** los archivos `.docx` temporales se eliminan con `deleteFileAfterSend(true)`.
7. **Escape:** siempre `setValue(..., true)` para evitar que un estudiante inyecte `{{OTRO_VAR}}` en su texto.

---

## Criterios de validación (checklist de prueba)

- [ ] `composer require phpoffice/phpword` sin errores.
- [ ] Migración `protocol_templates` se ejecuta y revertea limpiamente.
- [ ] Coordinador sube `.docx` con `{{NOMBRE}}`, `{{FECHA}}` → se detectan 2 placeholders y se crea schema por defecto.
- [ ] Coordinador edita label de `{{NOMBRE}}` a "Nombre completo", marca `required`, tipo `text`, maxChars 100 → se guarda en `schema_json`.
- [ ] Estudiante abre `/protocolo/llenar` → ve el formulario con los campos del schema.
- [ ] Estudiante deja un campo requerido vacío y pulsa "Previsualizar" → error de validación, no se genera archivo.
- [ ] Estudiante llena todos los campos y pulsa "Previsualizar" → descarga `.docx` con los valores rellenados en lugar de los placeholders.
- [ ] Estudiante intenta inyectar `{{INYECTADO}}` dentro de un campo de texto → el valor se inserta como texto literal, no se reemplaza como placeholder.
- [ ] Sin plantilla activa → mensaje amigable en `/protocolo/llenar`.

---

## Out of scope (Fase 2)

- LibreOffice headless → conversión a PDF.
- Colas / Horizon / polling de previews.
- Tabla `submissions` / `project_documents` vinculada a plantillas.
- Botón "Enviar protocolo" final que guarde el PDF en el proyecto.
- Integración con `EvaluationStage`, `ProjectEvaluation`.

---

## Archivos a crear / modificar

**Crear:**
- `.kilo/plans/protocolo-phpword-mvp.md` (este plan)
- `database/migrations/xxxx_xx_xx_xxxxxx_create_protocol_templates_table.php`
- `app/Models/ProtocolTemplate.php`
- `app/Http/Controllers/AdminProtocolTemplateController.php`
- `app/Http/Controllers/ProtocolPreviewController.php`
- `resources/views/admin/protocol-template.blade.php`
- `resources/views/protocol/form.blade.php`

**Modificar:**
- `composer.json` (agregar `phpoffice/phpword`)
- `routes/web.php` (agregar rutas del MVP)

**No tocar:**
- `ProjectDocument`, `Project`, `EvaluationStage`, `ProjectEvaluation`, `DocumentType`, `routes/auth.php`, políticas existentes.

---

Este plan está listo para ser implementado por un agente con permisos de escritura. Cuando quieras pasar a la implementación, indícame y cambiaré al agente correspondiente.