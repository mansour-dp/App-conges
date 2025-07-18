<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'titre',
        'message',
        'type',
        'lu',
        'data',
    ];

    protected $casts = [
        'lu' => 'boolean',
        'data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeNonLu($query)
    {
        return $query->where('lu', false);
    }
}
