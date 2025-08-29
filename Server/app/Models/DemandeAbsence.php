<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeAbsence extends Model
{
    use HasFactory;

    protected $table = 'demandes_absences';

    protected $fillable = [
        'user_id',
        'type_absence',
        'date_matin',
        'date_apres_midi',
        'date_journee',
        'periode_debut',
        'periode_fin',
        'nb_jours_deductibles',
        'motif',
        'commentaire',
        'statut',
        'valide_par',
        'date_validation',
        'commentaire_validation',
        'signatures',
        'signature_interresse',
        'pieces_jointes',
        'validation_workflow',
        'current_validator',
        'workflow_step',
        'date_soumission',
        'form_data',
    ];

    protected $casts = [
        'date_matin' => 'date',
        'date_apres_midi' => 'date',
        'date_journee' => 'date',
        'periode_debut' => 'date',
        'periode_fin' => 'date',
        'date_validation' => 'datetime',
        'date_soumission' => 'datetime',
        'signatures' => 'array',
        'pieces_jointes' => 'array',
        'validation_workflow' => 'array',
        'form_data' => 'array',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function validateur()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function currentValidator()
    {
        return $this->belongsTo(User::class, 'current_validator');
    }

    // Scopes
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeApprouve($query)
    {
        return $query->where('statut', 'approuve');
    }

    public function scopeRejete($query)
    {
        return $query->where('statut', 'rejete');
    }

    public function scopeParUtilisateur($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeParValidateur($query, $validatorId)
    {
        return $query->where('current_validator', $validatorId);
    }

    // Accesseurs
    public function getFullUserNameAttribute()
    {
        return $this->user ? $this->user->full_name : 'Utilisateur inconnu';
    }

    public function getTypeAbsenceLibelleAttribute()
    {
        return [
            'absence_exceptionnelle' => 'Absence exceptionnelle',
            'absence_maladie' => 'Absence maladie',
            'absence_formation' => 'Absence formation',
            'absence_mission' => 'Absence mission',
            'absence_personnelle' => 'Absence personnelle',
        ][$this->type_absence] ?? $this->type_absence;
    }

    public function getStatutLibelleAttribute()
    {
        return [
            'en_attente' => 'En attente',
            'approuve' => 'Approuvé',
            'rejete' => 'Rejeté',
            'annule' => 'Annulé',
        ][$this->statut] ?? $this->statut;
    }

    // Calculer la durée automatiquement
    public function getDureeJoursAttribute()
    {
        if ($this->periode_debut && $this->periode_fin) {
            return $this->periode_debut->diffInDays($this->periode_fin) + 1;
        }
        
        if ($this->date_journee) {
            return 1;
        }
        
        if ($this->date_matin || $this->date_apres_midi) {
            return 0.5;
        }
        
        return $this->nb_jours_deductibles ?? 0;
    }
}
