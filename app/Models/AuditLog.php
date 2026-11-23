<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'action', 'description', 'ip_address'])]
class AuditLog extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        // La auditoría es append-only: no se edita ni se elimina desde la app.
        static::updating(fn () => false);
        static::deleting(fn () => false);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Método Helper para registrar eventos fácilmente
    public static function record($action, $description)
    {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }
}