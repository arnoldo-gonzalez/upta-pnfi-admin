<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nombre', 'docx_path', 'schema_json', 'activo', 'created_by'])]
class ProtocolTemplate extends Model
{
    protected $casts = [
        'schema_json' => 'array',
    ];

    public function getSchemaAttribute()
    {
        return $this->schema_json;
    }

    public static function active()
    {
        return self::where('activo', true)->latest('id')->first();
    }
}