<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'create user',
            'edit user',
            'delete user',
            'view user',
            'export user',
            'import user',
            'create vehicle',
            'edit vehicle',
            'delete vehicle',
            'view vehicle',
            'export vehicle',
            'import vehicle',
            'create vehicle_type',
            'edit vehicle_type',
            'delete vehicle_type',
            'view vehicle_type',
            'export vehicle_type',
            'import vehicle_type',
            'create area',
            'edit area',
            'delete area',
            'view area',
            'export area',
            'import area',
            'create region',
            'edit region',
            'delete region',
            'view region',
            'export region',
            'import region',
            'create transporter',
            'edit transporter',
            'delete transporter',
            'view transporter',
            'export transporter',
            'import transporter',
            'create transporter_rate',
            'edit transporter_rate',
            'delete transporter_rate',
            'view transporter_rate',
            'export transporter_rate',
            'import transporter_rate',
            'can weight_in_rw',
            'can weight_out_rw',
            'can print_rw',
            'can weight_in_fg',
            'can weight_in_out',
            'can print_fg',
            'can approve',
            'can reject',
            'create role',
        ];

        $role = Role::findByName('SUPER_ADMIN'); // Get the role
        foreach ($permissions as $permission) {
            $permission = Permission::create(['name' => $permission]);
            $permission->assignRole($role);
            $permission->update();
        }
    }
}
