<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class LeavePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'days_count',
        'leave_type',
        'description'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    // Calculer automatiquement le nombre de jours
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($leavePlan) {
            if ($leavePlan->start_date && $leavePlan->end_date) {
                $start = new \DateTime($leavePlan->start_date);
                $end = new \DateTime($leavePlan->end_date);
                $leavePlan->days_count = $start->diff($end)->days + 1;
            }
        });
    }

    // Scope pour un type spécifique
    public function scopeOfType($query, $type)
    {
        return $query->where('leave_type', $type);
    }

    // Accesseur pour le label du type de congé
    public function getLeaveTypeLabelAttribute()
    {
        $labels = [
            'conge_annuel' => 'Congé annuel',
            'conges_fractionnes' => 'Congés fractionnés',
            'autres_conges_legaux' => 'Autres congés légaux',
            'conge_maladie' => 'Congé maladie',
            'conge_maternite' => 'Congé maternité',
            'conge_paternite' => 'Congé paternité',
            'conge_sans_solde' => 'Congé sans solde',
            'absence_exceptionnelle' => 'Absence exceptionnelle',
            'report_conge' => 'Report de congé'
        ];
        
        return $labels[$this->leave_type] ?? $this->leave_type;
    }
}
