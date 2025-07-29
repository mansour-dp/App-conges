<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'type',
        'description',
        'is_recurring'
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
    ];

    // Scope pour un type spécifique
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Scope pour les jours fériés à venir
    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString());
    }

    // Scope pour une année spécifique
    public function scopeForYear($query, $year)
    {
        return $query->whereYear('date', $year);
    }
}
