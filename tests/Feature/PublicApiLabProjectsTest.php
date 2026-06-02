<?php

namespace Tests\Feature;

use App\Enums\LabProjectStatus;
use App\Models\LabProject;
use App\Models\LabProjectTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\TestCase;

#[RequiresPhpExtension('pdo_sqlite')]
class PublicApiLabProjectsTest extends TestCase
{
    use RefreshDatabase;

    public function test_labs_list_returns_public_labs(): void
    {
        $building = $this->createLabProject('building-lab', LabProjectStatus::Building);
        LabProjectTranslation::query()->create([
            'lab_project_id' => $building->id,
            'locale' => 'en',
            'title' => 'Building Lab',
            'summary' => 'Visible building lab.',
        ]);

        $shipped = $this->createLabProject('shipped-lab', LabProjectStatus::Shipped);
        LabProjectTranslation::query()->create([
            'lab_project_id' => $shipped->id,
            'locale' => 'en',
            'title' => 'Shipped Lab',
            'summary' => 'Visible shipped lab.',
        ]);

        $response = $this->getJson('/api/v1/labs');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.resolvedLocale', 'en')
            ->assertJsonFragment(['status' => 'building'])
            ->assertJsonFragment(['status' => 'shipped']);
    }

    public function test_labs_list_hides_idea_paused_and_archived_labs(): void
    {
        $public = $this->createLabProject('building-lab', LabProjectStatus::Building);
        LabProjectTranslation::query()->create([
            'lab_project_id' => $public->id,
            'locale' => 'en',
            'title' => 'Building Lab',
        ]);

        foreach ([LabProjectStatus::Idea, LabProjectStatus::Paused, LabProjectStatus::Archived] as $status) {
            $hidden = $this->createLabProject($status->value.'-lab', $status);
            LabProjectTranslation::query()->create([
                'lab_project_id' => $hidden->id,
                'locale' => 'en',
                'title' => $status->value.' Lab',
            ]);
        }

        $response = $this->getJson('/api/v1/labs');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'building-lab')
            ->assertJsonMissing(['slug' => 'idea-lab'])
            ->assertJsonMissing(['slug' => 'paused-lab'])
            ->assertJsonMissing(['slug' => 'archived-lab']);
    }

    public function test_labs_list_uses_english_fallback_for_missing_french_fields(): void
    {
        $lab = $this->createLabProject('ai-sketchpad', LabProjectStatus::Building);
        LabProjectTranslation::query()->create([
            'lab_project_id' => $lab->id,
            'locale' => 'en',
            'title' => 'AI Sketchpad',
            'summary' => 'English summary.',
            'content' => 'English content.',
        ]);
        LabProjectTranslation::query()->create([
            'lab_project_id' => $lab->id,
            'locale' => 'fr',
            'title' => 'Carnet IA',
        ]);

        $response = $this->getJson('/api/v1/labs?locale=fr');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Carnet IA')
            ->assertJsonPath('data.0.summary', 'English summary.')
            ->assertJsonPath('data.0.content', 'English content.')
            ->assertJsonPath('meta.resolvedLocale', 'fr')
            ->assertJsonPath('meta.fallbackUsed', true)
            ->assertJsonPath('meta.fallbackFields', ['labs.0.summary', 'labs.0.content']);
    }

    public function test_labs_list_rejects_invalid_locale(): void
    {
        $response = $this->getJson('/api/v1/labs?locale=de');

        $response
            ->assertStatus(422)
            ->assertJsonPath('data', null)
            ->assertJsonPath('errors.locale.0', 'The locale must be one of: en, fr.')
            ->assertJsonPath('meta.locale', 'en')
            ->assertJsonPath('meta.requestedLocale', 'de')
            ->assertJsonPath('meta.resolvedLocale', 'en')
            ->assertJsonPath('meta.fallbackUsed', true);
    }

    private function createLabProject(string $slug, LabProjectStatus $status): LabProject
    {
        return LabProject::query()->create([
            'slug' => $slug,
            'status' => $status,
            'is_featured' => false,
            'published_at' => in_array($status, [LabProjectStatus::Building, LabProjectStatus::Shipped], true) ? now() : null,
        ]);
    }
}
