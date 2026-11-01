<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'description', 'start_date', 'event_type_id', 'visible_to_roles', 'role_links'])]
class Event extends Model
{
    use HasFactory;
    protected $casts = [
        'start_date' => 'datetime',
        'visible_to_roles' => 'array',
        'role_links' => 'array',
    ];

    public function type()
    {
        return $this->belongsTo(EventType::class, 'event_type_id');
    }
}