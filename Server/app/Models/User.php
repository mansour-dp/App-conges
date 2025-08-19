<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'first_name',
        'matricule',
        'email',
        'password',
        'phone',
        'fonction',
        'adresse',
        'department_id',
        'role_id',
        'manager_id',
        'is_active',
        'date_embauche',
        'conges_annuels_total',
        'conges_annuels_restants',
        'can_validate_leave',
        'is_manager',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_embauche' => 'date',
        'is_active' => 'boolean',
        'can_validate_leave' => 'boolean',
        'is_manager' => 'boolean',
    ];

    // Relations
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function demandesConges()
    {
        return $this->hasMany(DemandeConge::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function demandesValidees()
    {
        return $this->hasMany(DemandeConge::class, 'valide_par');
    }

    public function validations()
    {
        return $this->hasMany(DemandeConge::class, 'current_validator');
    }

    // Accesseurs
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->name;
    }

    // Méthodes utiles
    public function isManager()
    {
        return $this->subordinates()->exists();
    }

    public function canValidateLeave()
    {
        // Utiliser plusieurs conditions possibles
        if ($this->can_validate_leave || $this->is_manager) {
            return true;
        }
        
        if ($this->role) {
            return in_array($this->role->nom, ['Superieur', 'Directeur RH', 'Directeur Unité', 'Responsable RH', 'Admin', 'Manager']);
        }
        
        return false;
    }

    public function hasRole($roleName)
    {
        return $this->role?->nom === $roleName || $this->role?->name === $roleName;
    }
}
