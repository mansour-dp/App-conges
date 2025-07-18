<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    // Accesseur pour assurer la compatibilité avec 'name'
    public function getNameAttribute()
    {
        return $this->nom;
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
