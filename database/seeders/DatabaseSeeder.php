<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class, // Seeder de Roles e Permissões
            UserSeeder::class, // Criar utilizadores automaticamente
            EventosSeeder::class, // ✅ Inserir feriados nacionais e eventos da empresa
        ]);
    }
}
