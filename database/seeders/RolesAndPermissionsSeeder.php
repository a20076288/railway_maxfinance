<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage-users',
            'manage-companies',
            'view-dashboard',
            'request-leave',
            'approve-leave',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $superadmin = Role::firstOrCreate(['name' => 'superadmin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $colaborador = Role::firstOrCreate(['name' => 'colaborador']);

        $superadmin->givePermissionTo(Permission::all());
        $admin->givePermissionTo(['view-dashboard', 'approve-leave']);
        $colaborador->givePermissionTo(['view-dashboard', 'request-leave']);
    }
}
