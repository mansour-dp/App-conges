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
        'is_recurring',
        'is_active'
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Scope pour les jours fériés actifs
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

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
