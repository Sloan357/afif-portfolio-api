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
            SiteSettingsSeeder::class,
            HeroContentSeeder::class,
            TechnologySeeder::class,
            ExperienceSeeder::class,
            ProjectSeeder::class,
            LabProjectSeeder::class,
        ]);
    }
}
