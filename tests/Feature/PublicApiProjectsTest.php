<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\ProjectTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\TestCase;

#[RequiresPhpExtension('pdo_sqlite')]
class PublicApiProjectsTest extends TestCase
{
    use RefreshDatabase;

    public function test_projects_list_returns_published_projects(): void
    {
        $published = $this->createProject('published-project', ProjectStatus::Published);
        ProjectTranslation::query()->create([
            'project_id' => $published->id,
            'locale' => 'en',
            'title' => 'Published Project',
            'summary' => 'Visible summary.',
            'content' => 'Visible content.',
        ]);

        $response = $this->getJson('/api/v1/projects');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'published-project')
            ->assertJsonPath('data.0.title', 'Published Project')
            ->assertJsonPath('data.0.status', 'published')
            ->assertJsonPath('meta.resolvedLocale', 'en');
    }

    public function test_projects_list_hides_drafts(): void
    {
        $published = $this->createProject('published-project', ProjectStatus::Published);
        ProjectTranslation::query()->create([
            'project_id' => $published->id,
            'locale' => 'en',
            'title' => 'Published Project',
        ]);

        $draft = $this->createProject('draft-project', ProjectStatus::Draft);
        ProjectTranslation::query()->create([
            'project_id' => $draft->id,
            'locale' => 'en',
            'title' => 'Draft Project',
        ]);

        $response = $this->getJson('/api/v1/projects');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'published-project')
            ->assertJsonMissing(['slug' => 'draft-project']);
    }

    public function test_projects_detail_returns_published_project_by_slug(): void
    {
        $project = $this->createProject('portfolio-cms', ProjectStatus::Published);
        ProjectTranslation::query()->create([
            'project_id' => $project->id,
            'locale' => 'en',
            'title' => 'Portfolio CMS',
            'summary' => 'Project summary.',
            'content' => 'Project content.',
            'seo_title' => 'Portfolio CMS SEO',
            'seo_description' => 'SEO description.',
            'seo_keywords' => ['portfolio'],
        ]);

        $response = $this->getJson('/api/v1/projects/portfolio-cms');

        $response
            ->assertOk()
            ->assertJsonPath('data.slug', 'portfolio-cms')
            ->assertJsonPath('data.title', 'Portfolio CMS')
            ->assertJsonPath('data.content', 'Project content.')
            ->assertJsonPath('data.seo.title', 'Portfolio CMS SEO')
            ->assertJsonPath('data.links.self', url('/api/v1/projects/portfolio-cms'));
    }

    public function test_projects_detail_uses_english_fallback_for_missing_french_fields(): void
    {
        $project = $this->createProject('portfolio-cms', ProjectStatus::Published);
        ProjectTranslation::query()->create([
            'project_id' => $project->id,
            'locale' => 'en',
            'title' => 'Portfolio CMS',
            'summary' => 'English summary.',
            'content' => 'English content.',
        ]);
        ProjectTranslation::query()->create([
            'project_id' => $project->id,
            'locale' => 'fr',
            'title' => 'CMS de portfolio',
        ]);

        $response = $this->getJson('/api/v1/projects/portfolio-cms?locale=fr');

        $response
            ->assertOk()
            ->assertJsonPath('data.title', 'CMS de portfolio')
            ->assertJsonPath('data.summary', 'English summary.')
            ->assertJsonPath('data.content', 'English content.')
            ->assertJsonPath('meta.resolvedLocale', 'fr')
            ->assertJsonPath('meta.fallbackUsed', true)
            ->assertJsonPath('meta.fallbackFields', ['summary', 'content']);
    }

    public function test_projects_detail_returns_404_envelope_for_invalid_slug(): void
    {
        $this->createProject('portfolio-cms', ProjectStatus::Published);

        $response = $this->getJson('/api/v1/projects/missing-project');

        $response
            ->assertStatus(404)
            ->assertJsonPath('data', null)
            ->assertJsonPath('meta.resolvedLocale', 'en')
            ->assertJsonPath('links.self', url('/api/v1/projects/missing-project'));
    }

    private function createProject(string $slug, ProjectStatus $status): Project
    {
        return Project::query()->create([
            'slug' => $slug,
            'status' => $status,
            'is_featured' => false,
            'published_at' => $status === ProjectStatus::Published ? now() : null,
        ]);
    }
}
