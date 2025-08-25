<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LeavePlan;
use App\Models\Holiday;

class LeavePlansAndHolidaysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Création des périodes de congés par défaut
        LeavePlan::create([
            'name' => 'Congés d\'été 2025',
            'start_date' => '2025-07-01',
            'end_date' => '2025-08-31',
            'leave_type' => 'conge_annuel',
            'description' => 'Période principale des congés d\'été pour l\'année 2025'
        ]);

        LeavePlan::create([
            'name' => 'Congés de fin d\'année 2024',
            'start_date' => '2024-12-20',
            'end_date' => '2025-01-08',
            'leave_type' => 'conge_annuel',
            'description' => 'Période des congés de fin d\'année et nouvel an'
        ]);

        LeavePlan::create([
            'name' => 'Congés de Pâques 2025',
            'start_date' => '2025-04-14',
            'end_date' => '2025-04-21',
            'leave_type' => 'autres_conges_legaux',
            'description' => 'Période des congés de Pâques'
        ]);

        LeavePlan::create([
            'name' => 'Congés maternité - Marie Diop',
            'start_date' => '2025-03-01',
            'end_date' => '2025-05-30',
            'leave_type' => 'conge_maternite',
            'description' => 'Congé maternité de 3 mois'
        ]);

        LeavePlan::create([
            'name' => 'Congés fractionnés - Équipe IT',
            'start_date' => '2025-02-15',
            'end_date' => '2025-02-22',
            'leave_type' => 'conges_fractionnes',
            'description' => 'Congés fractionnés pour l\'équipe informatique'
        ]);

        // Création des jours fériés par défaut (Sénégal)
        Holiday::create([
            'name' => 'Nouvel An',
            'date' => '2025-01-01',
            'type' => 'national',
            'description' => 'Premier jour de l\'année',
            'is_recurring' => true
        ]);

        Holiday::create([
            'name' => 'Fête de l\'Indépendance',
            'date' => '2025-04-04',
            'type' => 'national',
            'description' => 'Fête nationale du Sénégal',
            'is_recurring' => true
        ]);

        Holiday::create([
            'name' => 'Fête du Travail',
            'date' => '2025-05-01',
            'type' => 'national',
            'description' => 'Journée internationale des travailleurs',
            'is_recurring' => true
        ]);

        Holiday::create([
            'name' => 'Lundi de Pâques',
            'date' => '2025-04-21',
            'type' => 'religious',
            'description' => 'Fête chrétienne',
            'is_recurring' => false
        ]);

        Holiday::create([
            'name' => 'Korité (Aïd el-Fitr)',
            'date' => '2025-03-31',
            'type' => 'religious',
            'description' => 'Fête musulmane de la fin du Ramadan',
            'is_recurring' => false
        ]);

        Holiday::create([
            'name' => 'Tabaski (Aïd el-Kebir)',
            'date' => '2025-06-07',
            'type' => 'religious',
            'description' => 'Fête du sacrifice dans l\'Islam',
            'is_recurring' => false
        ]);

        Holiday::create([
            'name' => 'Assomption',
            'date' => '2025-08-15',
            'type' => 'religious',
            'description' => 'Fête chrétienne de l\'Assomption',
            'is_recurring' => true
        ]);

        Holiday::create([
            'name' => 'Toussaint',
            'date' => '2025-11-01',
            'type' => 'religious',
            'description' => 'Fête de tous les saints',
            'is_recurring' => true
        ]);

        Holiday::create([
            'name' => 'Noël',
            'date' => '2025-12-25',
            'type' => 'religious',
            'description' => 'Fête de la nativité',
            'is_recurring' => true
        ]);
    }
}
