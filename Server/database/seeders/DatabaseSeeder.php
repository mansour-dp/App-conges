<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Appeler le UserSeeder qui gère tout de manière centralisée et professionnelle
        $this->call([
            UserSeeder::class,
        ]);
    }
}
