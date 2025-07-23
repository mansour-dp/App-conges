<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Appeler le UserSeeder 
        $this->call([
            UserSeeder::class,
        ]);
    }
}
