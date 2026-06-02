<?php

namespace Tests\Feature;

use App\Enums\HeroContentStatus;
use App\Enums\LabProjectStatus;
use App\Enums\ProjectStatus;
use App\Enums\TechnologyCategory;
use App\Models\Experience;
use App\Models\ExperienceTranslation;
use App\Models\HeroContent;
use App\Models\HeroContentTranslation;
use App\Models\LabProject;
use App\Models\LabProjectTranslation;
use App\Models\Project;
use App\Models\ProjectTranslation;
use App\Models\SiteSetting;
use App\Models\SiteSettingTranslation;
use App\Models\Technology;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicApiHomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_endpoint_returns_home_payload(): void
    {
        $this->createHomeContent();

        $response = $this->getJson('/api/v1/home');

        $response
            ->assertOk()
            ->assertJsonPath('data.settings.siteName', 'Afif Portfolio')
            ->assertJsonPath('data.hero.headline', 'Building practical software systems')
            ->assertJsonPath('data.featuredProjects.0.slug', 'portfolio-cms')
            ->assertJsonPath('data.labProjects.0.slug', 'ai-sketchpad')
            ->assertJsonPath('data.experience.0.company', 'Acme')
            ->assertJsonPath('data.technologies.0.slug', 'laravel')
            ->assertJsonPath('meta.resolvedLocale', 'en')
            ->assertJsonPath('links.self', url('/api/v1/home'))
            ->assertJsonMissingPath('data.settings.adminUrl')
            ->assertJsonMissingPath('data.hero.createdBy')
            ->assertJsonMissingPath('data.featuredProjects.0.createdBy')
            ->assertJsonMissingPath('data.labProjects.0.updatedBy')
            ->assertJsonMissingPath('data.experience.0.isVisible')
            ->assertJsonMissingPath('data.technologies.0.createdBy');
    }

    public function test_home_endpoint_respects_locale(): void
    {
        $this->createHomeContent(includeFrench: true);

        $response = $this->getJson('/api/v1/home?locale=fr');

        $response
            ->assertOk()
            ->assertJsonPath('data.settings.tagline', 'Ingenieur logiciel')
            ->assertJsonPath('data.hero.headline', 'Construire des systemes logiciels pratiques')
            ->assertJsonPath('data.featuredProjects.0.title', 'CMS de portfolio')
            ->assertJsonPath('data.labProjects.0.title', 'Carnet IA')
            ->assertJsonPath('data.experience.0.role', 'Ingenieur logiciel senior')
            ->assertJsonPath('meta.resolvedLocale', 'fr');
    }

    public function test_home_endpoint_hides_unpublished_content(): void
    {
        $this->createHomeContent();

        $draftProject = Project::query()->create([
            'slug' => 'draft-project',
            'status' => ProjectStatus::Draft,
            'is_featured' => true,
        ]);
        ProjectTranslation::query()->create([
            'project_id' => $draftProject->id,
            'locale' => 'en',
            'title' => 'Draft Project',
        ]);

        $ideaLab = LabProject::query()->create([
            'slug' => 'idea-lab',
            'status' => LabProjectStatus::Idea,
            'is_featured' => true,
        ]);
        LabProjectTranslation::query()->create([
            'lab_project_id' => $ideaLab->id,
            'locale' => 'en',
            'title' => 'Idea Lab',
        ]);

        $hiddenExperience = Experience::query()->create([
            'company' => 'Hidden Co',
            'is_visible' => false,
        ]);
        ExperienceTranslation::query()->create([
            'experience_id' => $hiddenExperience->id,
            'locale' => 'en',
            'role' => 'Hidden Role',
        ]);

        Technology::query()->create([
            'slug' => 'hidden-tech',
            'name' => 'Hidden Tech',
            'is_visible' => false,
        ]);

        $response = $this->getJson('/api/v1/home');

        $response
            ->assertOk()
            ->assertJsonMissing(['slug' => 'draft-project'])
            ->assertJsonMissing(['slug' => 'idea-lab'])
            ->assertJsonMissing(['company' => 'Hidden Co'])
            ->assertJsonMissing(['slug' => 'hidden-tech']);
    }

    public function test_home_endpoint_includes_fallback_metadata(): void
    {
        $this->createHomeContent(includeFrench: true, partialFrench: true);

        $response = $this->getJson('/api/v1/home?locale=fr');

        $response
            ->assertOk()
            ->assertJsonPath('data.hero.headline', 'Construire des systemes logiciels pratiques')
            ->assertJsonPath('data.hero.description', 'Portfolio CMS and engineering notes.')
            ->assertJsonPath('data.featuredProjects.0.summary', 'A Laravel Filament CMS.')
            ->assertJsonPath('meta.resolvedLocale', 'fr')
            ->assertJsonPath('meta.fallbackUsed', true);

        $fallbackFields = $response->json('meta.fallbackFields');

        $this->assertContains('hero.description', $fallbackFields);
        $this->assertContains('featuredProjects.0.summary', $fallbackFields);
        $this->assertContains('labProjects.0.content', $fallbackFields);
        $this->assertContains('experience.0.summary', $fallbackFields);
    }

    private function createHomeContent(bool $includeFrench = false, bool $partialFrench = false): void
    {
        $settings = SiteSetting::query()->create([
            'site_name' => 'Afif Portfolio',
            'is_active' => true,
            'email' => 'hello@example.com',
            'social_links' => [['platform' => 'github', 'url' => 'https://github.com/example']],
            'contact_links' => [['type' => 'email', 'url' => 'mailto:hello@example.com']],
        ]);
        SiteSettingTranslation::query()->create([
            'site_setting_id' => $settings->id,
            'locale' => 'en',
            'tagline' => 'Software engineer',
            'description' => 'Building useful web software.',
            'default_seo_title' => 'Afif Portfolio',
            'default_seo_description' => 'Portfolio and engineering notes.',
            'default_seo_keywords' => ['portfolio', 'software'],
        ]);

        if ($includeFrench) {
            SiteSettingTranslation::query()->create([
                'site_setting_id' => $settings->id,
                'locale' => 'fr',
                'tagline' => 'Ingenieur logiciel',
                'description' => $partialFrench ? null : 'Construction de logiciels utiles.',
                'default_seo_title' => $partialFrench ? null : 'Portfolio Afif',
                'default_seo_description' => $partialFrench ? null : 'Portfolio et notes techniques.',
                'default_seo_keywords' => $partialFrench ? null : ['portfolio', 'logiciel'],
            ]);
        }

        $hero = HeroContent::query()->create([
            'status' => HeroContentStatus::Published,
            'is_active' => true,
            'primary_cta_url' => '/projects',
            'secondary_cta_url' => '/blog',
            'published_at' => now(),
        ]);
        HeroContentTranslation::query()->create([
            'hero_content_id' => $hero->id,
            'locale' => 'en',
            'badge' => 'Available for selected work',
            'headline' => 'Building practical software systems',
            'description' => 'Portfolio CMS and engineering notes.',
            'primary_cta_label' => 'View projects',
            'secondary_cta_label' => 'Read notes',
            'capabilities' => ['Laravel APIs'],
            'architecture_items' => [['title' => 'CMS']],
        ]);

        if ($includeFrench) {
            HeroContentTranslation::query()->create([
                'hero_content_id' => $hero->id,
                'locale' => 'fr',
                'badge' => $partialFrench ? null : 'Disponible',
                'headline' => 'Construire des systemes logiciels pratiques',
                'description' => $partialFrench ? null : 'CMS portfolio et notes techniques.',
                'primary_cta_label' => $partialFrench ? null : 'Voir les projets',
                'secondary_cta_label' => $partialFrench ? null : 'Lire les notes',
                'capabilities' => $partialFrench ? null : ['API Laravel'],
                'architecture_items' => $partialFrench ? null : [['title' => 'CMS']],
            ]);
        }

        $project = Project::query()->create([
            'slug' => 'portfolio-cms',
            'status' => ProjectStatus::Published,
            'is_featured' => true,
            'sort_order' => 1,
            'published_at' => now(),
        ]);
        ProjectTranslation::query()->create([
            'project_id' => $project->id,
            'locale' => 'en',
            'title' => 'Portfolio CMS',
            'summary' => 'A Laravel Filament CMS.',
            'content' => 'Detailed project write-up.',
            'seo_title' => 'Portfolio CMS case study',
            'seo_description' => 'How the CMS was built.',
            'seo_keywords' => ['laravel'],
        ]);

        if ($includeFrench) {
            ProjectTranslation::query()->create([
                'project_id' => $project->id,
                'locale' => 'fr',
                'title' => 'CMS de portfolio',
                'summary' => $partialFrench ? null : 'Un CMS Laravel Filament.',
                'content' => $partialFrench ? null : 'Etude de cas detaillee.',
                'seo_title' => $partialFrench ? null : 'Etude CMS portfolio',
                'seo_description' => $partialFrench ? null : 'Construction du CMS.',
                'seo_keywords' => $partialFrench ? null : ['laravel'],
            ]);
        }

        $lab = LabProject::query()->create([
            'slug' => 'ai-sketchpad',
            'status' => LabProjectStatus::Building,
            'is_featured' => true,
            'sort_order' => 1,
            'published_at' => now(),
        ]);
        LabProjectTranslation::query()->create([
            'lab_project_id' => $lab->id,
            'locale' => 'en',
            'title' => 'AI Sketchpad',
            'summary' => 'A lab for AI-assisted ideation.',
            'content' => 'Detailed lab notes.',
            'problem' => 'Exploring better workflows.',
            'approach' => 'Prototype small tools.',
            'architecture_notes' => 'Laravel API with Next.js UI.',
            'seo_title' => 'AI Sketchpad Lab',
            'seo_description' => 'Lab project notes.',
            'seo_keywords' => ['ai'],
        ]);

        if ($includeFrench) {
            LabProjectTranslation::query()->create([
                'lab_project_id' => $lab->id,
                'locale' => 'fr',
                'title' => 'Carnet IA',
                'summary' => $partialFrench ? null : 'Un laboratoire IA.',
                'content' => $partialFrench ? null : 'Notes detaillees.',
                'problem' => $partialFrench ? null : 'Explorer les flux de travail.',
                'approach' => $partialFrench ? null : 'Prototyper de petits outils.',
                'architecture_notes' => $partialFrench ? null : 'API Laravel avec UI Next.js.',
                'seo_title' => $partialFrench ? null : 'Laboratoire Carnet IA',
                'seo_description' => $partialFrench ? null : 'Notes du laboratoire.',
                'seo_keywords' => $partialFrench ? null : ['ia'],
            ]);
        }

        $experience = Experience::query()->create([
            'company' => 'Acme',
            'company_url' => 'https://acme.example.com',
            'location' => 'Remote',
            'start_date' => '2022-01-01',
            'is_current' => true,
            'sort_order' => 1,
            'is_visible' => true,
        ]);
        ExperienceTranslation::query()->create([
            'experience_id' => $experience->id,
            'locale' => 'en',
            'role' => 'Senior Software Engineer',
            'summary' => 'Built product platforms.',
            'responsibilities' => ['Led API design'],
        ]);

        if ($includeFrench) {
            ExperienceTranslation::query()->create([
                'experience_id' => $experience->id,
                'locale' => 'fr',
                'role' => 'Ingenieur logiciel senior',
                'summary' => $partialFrench ? null : 'Construction de plateformes.',
                'responsibilities' => $partialFrench ? null : ['Direction API'],
            ]);
        }

        Technology::query()->create([
            'slug' => 'laravel',
            'name' => 'Laravel',
            'category' => TechnologyCategory::Framework,
            'sort_order' => 1,
            'is_visible' => true,
        ]);
    }
}
