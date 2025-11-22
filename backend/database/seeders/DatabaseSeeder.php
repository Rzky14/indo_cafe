<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles first (required for user role assignments)
        $this->call([
            RoleSeeder::class,
            MenuItemSeeder::class,
        ]);
    }
}
