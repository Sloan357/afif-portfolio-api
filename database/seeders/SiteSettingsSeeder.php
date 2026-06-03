<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use App\Models\SiteSettingTranslation;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = SiteSetting::query()->updateOrCreate(
            ['key' => 'default'],
            [
                'site_name' => 'Afif El Charif',
                'is_active' => true,
                'email' => 'hello@example.com',
                'phone' => null,
                'location' => null,
                'primary_domain' => 'afifelcharif.com',
                'frontend_url' => 'https://afifelcharif.com',
                'admin_url' => null,
                'default_og_image_id' => null,
                'favicon_media_id' => null,
                'social_links' => [
                    [
                        'platform' => 'github',
                        'label' => 'GitHub',
                        'url' => 'https://github.com/afifelcharif',
                    ],
                    [
                        'platform' => 'linkedin',
                        'label' => 'LinkedIn',
                        'url' => 'https://www.linkedin.com/in/TODO',
                    ],
                ],
                'contact_links' => [
                    [
                        'type' => 'email',
                        'label' => 'Email me',
                        'value' => 'hello@example.com',
                        'url' => 'mailto:hello@example.com',
                    ],
                    [
                        'type' => 'cv',
                        'label' => 'Download CV',
                        'value' => null,
                        'url' => '/cv/afif-el-charif-cv.pdf',
                    ],
                ],
                'metadata' => [
                    'source' => 'frontend-static-content',
                    'placeholders' => ['email', 'linkedin', 'cv'],
                ],
            ],
        );

        $this->translation($settings, 'en', [
            'tagline' => 'Full-Stack Software Engineer',
            'description' => 'Portfolio focused on Laravel, Symfony, React Native, Next.js, AI integrations, CMS platforms, and production deployments.',
            'default_seo_title' => 'Afif El Charif - Full-Stack Software Engineer',
            'default_seo_description' => 'Portfolio of Afif El Charif, a full-stack software engineer focused on Laravel, Symfony, React Native, Next.js, AI integrations, CMS platforms, and production deployments.',
            'default_seo_keywords' => ['Laravel', 'Symfony', 'React Native', 'Next.js', 'AI integrations', 'CMS', 'deployments'],
            'footer_text' => 'Have a system, product, or workflow worth building?',
        ]);

        $this->translation($settings, 'fr', [
            'tagline' => 'Ingenieur logiciel full-stack',
            'description' => 'Portfolio centre sur Laravel, Symfony, React Native, Next.js, integrations IA, plateformes CMS et deploiements en production.',
            'default_seo_title' => 'Afif El Charif - Ingenieur logiciel full-stack',
            'default_seo_description' => "Portfolio d'Afif El Charif, ingenieur logiciel full-stack specialise en Laravel, Symfony, React Native, Next.js, integrations IA, plateformes CMS et deploiements en production.",
            'default_seo_keywords' => ['Laravel', 'Symfony', 'React Native', 'Next.js', 'integrations IA', 'CMS', 'deploiements'],
            'footer_text' => 'Vous avez un systeme, un produit ou un workflow a construire ?',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function translation(SiteSetting $settings, string $locale, array $data): void
    {
        SiteSettingTranslation::query()->updateOrCreate(
            [
                'site_setting_id' => $settings->id,
                'locale' => $locale,
            ],
            $data,
        );
    }
}
