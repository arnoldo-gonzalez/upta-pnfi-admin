<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name'])]
class EventType extends Model
{
    use HasFactory;

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}