<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Database\Seeders\TransporterRateSeeder as SeedersTransporterRateSeeder;
use Illuminate\Database\Seeder;
use TransporterRateSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            VehicleTypeSeeder::class,
            RegionSeeder::class,
            AreaSeeder::class,
            TransporterSeeder::class,
            SeedersTransporterRateSeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            UserSeeder::class,
        ]);
    }
}
