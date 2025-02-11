<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;


class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Criar roles sem permissões individuais
        Role::firstOrCreate(['name' => 'superadmin']);
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'colaborador']);
    }
}
