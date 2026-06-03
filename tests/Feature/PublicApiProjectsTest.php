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

    public function test_projects_list_returns_paginated_envelope(): void
    {
        foreach (range(1, 13) as $index) {
            $project = $this->createProject('project-'.$index, ProjectStatus::Published);
            ProjectTranslation::query()->create([
                'project_id' => $project->id,
                'locale' => 'en',
                'title' => 'Project '.$index,
            ]);
        }

        $response = $this->getJson('/api/v1/projects');

        $response
            ->assertOk()
            ->assertJsonCount(12, 'data')
            ->assertJsonPath('meta.pagination.currentPage', 1)
            ->assertJsonPath('meta.pagination.perPage', 12)
            ->assertJsonPath('meta.pagination.lastPage', 2)
            ->assertJsonPath('meta.pagination.total', 13)
            ->assertJsonPath('meta.pagination.from', 1)
            ->assertJsonPath('meta.pagination.to', 12)
            ->assertJsonStructure([
                'links' => ['self', 'first', 'last', 'prev', 'next'],
            ]);
    }

    public function test_projects_page_changes_records(): void
    {
        foreach (range(1, 3) as $index) {
            $project = $this->createProject('project-'.$index, ProjectStatus::Published, $index);
            ProjectTranslation::query()->create([
                'project_id' => $project->id,
                'locale' => 'en',
                'title' => 'Project '.$index,
            ]);
        }

        $firstPage = $this->getJson('/api/v1/projects?perPage=1&page=1');
        $secondPage = $this->getJson('/api/v1/projects?perPage=1&page=2');

        $firstPage->assertJsonPath('data.0.slug', 'project-1');
        $secondPage
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'project-2')
            ->assertJsonPath('meta.pagination.currentPage', 2)
            ->assertJsonPath('links.prev', url('/api/v1/projects?perPage=1&page=1'));
    }

    public function test_projects_per_page_controls_size(): void
    {
        foreach (range(1, 3) as $index) {
            $project = $this->createProject('project-'.$index, ProjectStatus::Published, $index);
            ProjectTranslation::query()->create([
                'project_id' => $project->id,
                'locale' => 'en',
                'title' => 'Project '.$index,
            ]);
        }

        $response = $this->getJson('/api/v1/projects?perPage=2');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.pagination.perPage', 2)
            ->assertJsonPath('meta.pagination.lastPage', 2);
    }

    public function test_projects_invalid_page_returns_validation_error(): void
    {
        $response = $this->getJson('/api/v1/projects?page=0');

        $response
            ->assertStatus(422)
            ->assertJsonPath('data', null)
            ->assertJsonPath('errors.page.0', 'The page must be an integer greater than or equal to 1.');
    }

    public function test_projects_invalid_per_page_returns_validation_error(): void
    {
        $response = $this->getJson('/api/v1/projects?perPage=abc');

        $response
            ->assertStatus(422)
            ->assertJsonPath('data', null)
            ->assertJsonPath('errors.perPage.0', 'The perPage must be an integer between 1 and 50.');
    }

    public function test_projects_per_page_max_is_enforced(): void
    {
        $response = $this->getJson('/api/v1/projects?perPage=51');

        $response
            ->assertStatus(422)
            ->assertJsonPath('errors.perPage.0', 'The perPage must be an integer between 1 and 50.');
    }


    public function test_projects_list_hides_review_archived_and_future_published_projects(): void
    {
        $published = $this->createProject('published-project', ProjectStatus::Published);
        ProjectTranslation::query()->create([
            'project_id' => $published->id,
            'locale' => 'en',
            'title' => 'Published Project',
        ]);

        foreach ([ProjectStatus::Review, ProjectStatus::Archived] as $status) {
            $hidden = $this->createProject($status->value.'-project', $status);
            ProjectTranslation::query()->create([
                'project_id' => $hidden->id,
                'locale' => 'en',
                'title' => $status->value.' Project',
            ]);
        }

        $future = Project::query()->create([
            'slug' => 'future-project',
            'status' => ProjectStatus::Published,
            'published_at' => now()->addDay(),
        ]);
        ProjectTranslation::query()->create([
            'project_id' => $future->id,
            'locale' => 'en',
            'title' => 'Future Project',
        ]);

        $response = $this->getJson('/api/v1/projects');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'published-project')
            ->assertJsonMissing(['slug' => 'review-project'])
            ->assertJsonMissing(['slug' => 'archived-project'])
            ->assertJsonMissing(['slug' => 'future-project']);
    }

    public function test_projects_detail_returns_404_envelope_for_unpublished_slug(): void
    {
        $hiddenProjects = [
            $this->createProject('draft-project', ProjectStatus::Draft),
            $this->createProject('review-project', ProjectStatus::Review),
            $this->createProject('archived-project', ProjectStatus::Archived),
            Project::query()->create([
                'slug' => 'future-project',
                'status' => ProjectStatus::Published,
                'published_at' => now()->addDay(),
            ]),
        ];

        foreach ($hiddenProjects as $project) {
            ProjectTranslation::query()->create([
                'project_id' => $project->id,
                'locale' => 'en',
                'title' => 'Hidden Project',
            ]);

            $response = $this->getJson('/api/v1/projects/'.$project->slug);

            $response
                ->assertStatus(404)
                ->assertJsonPath('data', null)
                ->assertJsonPath('meta.resolvedLocale', 'en')
                ->assertJsonPath('links.self', url('/api/v1/projects/'.$project->slug));
        }
    }

    public function test_projects_paginated_list_includes_fallback_metadata(): void
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

        $response = $this->getJson('/api/v1/projects?locale=fr&perPage=1');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.title', 'CMS de portfolio')
            ->assertJsonPath('data.0.summary', 'English summary.')
            ->assertJsonPath('data.0.content', 'English content.')
            ->assertJsonPath('meta.fallbackUsed', true)
            ->assertJsonPath('meta.pagination.perPage', 1)
            ->assertJsonPath('links.self', url('/api/v1/projects?locale=fr&perPage=1&page=1'));

        $fallbackFields = $response->json('meta.fallbackFields');

        $this->assertContains('projects.0.summary', $fallbackFields);
        $this->assertContains('projects.0.content', $fallbackFields);
    }

    public function test_projects_pagination_handles_out_of_range_page(): void
    {
        $project = $this->createProject('project-1', ProjectStatus::Published);
        ProjectTranslation::query()->create([
            'project_id' => $project->id,
            'locale' => 'en',
            'title' => 'Project 1',
        ]);

        $response = $this->getJson('/api/v1/projects?page=99&perPage=1');

        $response
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.pagination.currentPage', 99)
            ->assertJsonPath('meta.pagination.lastPage', 1)
            ->assertJsonPath('meta.pagination.from', null)
            ->assertJsonPath('meta.pagination.to', null)
            ->assertJsonPath('links.next', null);
    }

    public function test_projects_pagination_accepts_max_per_page(): void
    {
        foreach (range(1, 3) as $index) {
            $project = $this->createProject('max-project-'.$index, ProjectStatus::Published, $index);
            ProjectTranslation::query()->create([
                'project_id' => $project->id,
                'locale' => 'en',
                'title' => 'Max Project '.$index,
            ]);
        }

        $response = $this->getJson('/api/v1/projects?perPage=50');

        $response
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.pagination.perPage', 50);
    }

    public function test_projects_pagination_rejects_array_parameters(): void
    {
        $this->getJson('/api/v1/projects?page[]=1')
            ->assertStatus(422)
            ->assertJsonPath('data', null)
            ->assertJsonPath('errors.page.0', 'The page must be an integer greater than or equal to 1.');

        $this->getJson('/api/v1/projects?perPage[]=12')
            ->assertStatus(422)
            ->assertJsonPath('data', null)
            ->assertJsonPath('errors.perPage.0', 'The perPage must be an integer between 1 and 50.');
    }

    private function createProject(string $slug, ProjectStatus $status, ?int $sortOrder = null): Project
    {
        return Project::query()->create([
            'slug' => $slug,
            'status' => $status,
            'is_featured' => false,
            'sort_order' => $sortOrder,
            'published_at' => $status === ProjectStatus::Published ? now() : null,
        ]);
    }
}
