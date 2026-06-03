<?php

namespace Database\Seeders;

use App\Enums\HeroContentStatus;
use App\Models\HeroContent;
use App\Models\HeroContentTranslation;
use Illuminate\Database\Seeder;

class HeroContentSeeder extends Seeder
{
    public function run(): void
    {
        $hero = HeroContent::query()->updateOrCreate(
            ['key' => 'homepage'],
            [
                'status' => HeroContentStatus::Published,
                'is_active' => true,
                'primary_cta_url' => '#projects',
                'secondary_cta_url' => '#contact',
                'hero_image_id' => null,
                'og_image_id' => null,
                'sort_order' => 1,
                'published_at' => now(),
            ],
        );

        $this->translation($hero, 'en', [
            'badge' => 'Full-stack software engineer | APIs, systems, interfaces',
            'headline' => 'I build reliable software across backend, web, and mobile.',
            'description' => "I'm Afif. I work close to the data model, API boundaries, and frontend runtime to ship systems that are maintainable under real usage: Laravel, Symfony, React, React Native, and AI-integrated workflows.",
            'primary_cta_label' => 'Inspect projects',
            'secondary_cta_label' => 'Contact',
            'capabilities' => [
                ['label' => 'Backend', 'value' => 'Laravel, Symfony, REST APIs'],
                ['label' => 'Frontend', 'value' => 'React, Next.js, stateful UI'],
                ['label' => 'Mobile', 'value' => 'React Native, app workflows'],
            ],
            'architecture_items' => [
                ['label' => 'Architecture', 'value' => 'Product system card'],
                ['label' => 'Status', 'value' => 'API-first'],
                ['label' => 'Clients', 'value' => 'Web app, Mobile app'],
                ['label' => 'Core', 'value' => 'Laravel / Symfony API /v1'],
                ['label' => 'Auth', 'value' => 'Sessions, roles'],
                ['label' => 'API', 'value' => 'REST contracts'],
                ['label' => 'Jobs', 'value' => 'Queues, workers'],
                ['label' => 'MariaDB', 'value' => 'Relational data model'],
                ['label' => 'AI layer', 'value' => 'Tool-backed workflows'],
                ['label' => 'Boundaries', 'value' => 'UI - API - DATA'],
            ],
        ]);

        $this->translation($hero, 'fr', [
            'badge' => 'Ingenieur logiciel full-stack | APIs, systemes, interfaces',
            'headline' => 'Je construis des logiciels fiables pour le backend, le web et le mobile.',
            'description' => "Je suis Afif. Je travaille au plus pres du modele de donnees, des frontieres API et du runtime frontend pour livrer des systemes maintenables en usage reel : Laravel, Symfony, React, React Native et workflows integres a l'IA.",
            'primary_cta_label' => 'Voir les projets',
            'secondary_cta_label' => 'Contact',
            'capabilities' => [
                ['label' => 'Backend', 'value' => 'Laravel, Symfony, REST APIs'],
                ['label' => 'Frontend', 'value' => 'React, Next.js, UI avec etat'],
                ['label' => 'Mobile', 'value' => 'React Native, workflows applicatifs'],
            ],
            'architecture_items' => null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function translation(HeroContent $hero, string $locale, array $data): void
    {
        HeroContentTranslation::query()->updateOrCreate(
            [
                'hero_content_id' => $hero->id,
                'locale' => $locale,
            ],
            $data,
        );
    }
}
