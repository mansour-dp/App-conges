<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'titre',
        'message',
        'type',
        'lu',
        'is_read',
        'data',
        'read_at',
    ];

    protected $casts = [
        'lu' => 'boolean',
        'is_read' => 'boolean',
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeNonLu($query)
    {
        return $query->where('lu', false);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false)->orWhere('lu', false);
    }

    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'lu' => true,
            'read_at' => now(),
        ]);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
