<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeConge extends Model
{
    use HasFactory;

    protected $table = 'demandes_conges';

    protected $fillable = [
        'user_id',
        'type_demande',
        'date_debut',
        'date_fin',
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
        'date_debut' => 'date',
        'date_fin' => 'date',
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

    public function validateurFinal()
    {
        return $this->belongsTo(User::class, 'valide_par');
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

    // Accesseurs
    public function getStatutLabelAttribute()
    {
        return [
            'brouillon' => 'Brouillon',
            'en_attente' => 'En attente',
            'en_attente_superieur' => 'En attente supérieur',
            'en_attente_directeur_unite' => 'En attente directeur unité',
            'en_attente_responsable_rh' => 'En attente responsable RH',
            'en_attente_directeur_rh' => 'En attente directeur RH',
            'approuve' => 'Approuvé',
            'rejete' => 'Rejeté',
            'annule' => 'Annulé',
        ][$this->statut] ?? $this->statut;
    }

    public function getTypeLabelAttribute()
    {
        return [
            'conge_annuel' => 'Congé annuel',
            'conge_maladie' => 'Congé maladie',
            'conge_maternite' => 'Congé maternité',
            'conge_paternite' => 'Congé paternité',
            'conge_sans_solde' => 'Congé sans solde',
            'absence_exceptionnelle' => 'Absence exceptionnelle',
            'report_conge' => 'Report de congé',
        ][$this->type_demande] ?? $this->type_demande;
    }
}
