<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed a sample user
        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'super-admin-wb@gmail.com',
            'password' => Hash::make('rahasialah'), // Hashed password
            'is_active' => true, // Optional if you have this field
        ]);

        $role = Role::findByName('SUPER_ADMIN'); // Get the role
        $user->assignRole($role);
        $user->update();
    }
}
