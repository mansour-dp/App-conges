<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeReport extends Model
{
    use HasFactory;

    protected $table = 'demandes_reports';

    protected $fillable = [
        'user_id',
        'type_demande',
        'date_conge_drh',
        'date_depart_prevue',
        'nouvelle_date_debut',
        'nouvelle_date_fin',
        'duree_jours',
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
        'date_conge_drh' => 'date',
        'date_depart_prevue' => 'date',
        'nouvelle_date_debut' => 'date',
        'nouvelle_date_fin' => 'date',
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

    public function getTypeDemandeLibelleAttribute()
    {
        return [
            'report_conge' => 'Report de congés',
            'report_conge_annuel' => 'Report congé annuel',
            'report_conge_maladie' => 'Report congé maladie',
            'report_conge_exceptionnel' => 'Report congé exceptionnel',
        ][$this->type_demande] ?? $this->type_demande;
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
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($demandeReport) {
            if ($demandeReport->nouvelle_date_debut && $demandeReport->nouvelle_date_fin) {
                $debut = new \DateTime($demandeReport->nouvelle_date_debut);
                $fin = new \DateTime($demandeReport->nouvelle_date_fin);
                $demandeReport->duree_jours = $debut->diff($fin)->days + 1;
            }
        });
    }
}
