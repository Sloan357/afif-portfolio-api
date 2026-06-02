<?php

namespace Tests\Feature;

use App\Models\Experience;
use App\Models\ExperienceTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicApiExperiencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_experience_list_returns_visible_experiences(): void
    {
        $experience = $this->createExperience('Acme', true, 1, '2022-01-01');
        ExperienceTranslation::query()->create([
            'experience_id' => $experience->id,
            'locale' => 'en',
            'role' => 'Senior Software Engineer',
            'summary' => 'Built product platforms.',
            'responsibilities' => ['Led API design'],
        ]);

        $response = $this->getJson('/api/v1/experience');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.company', 'Acme')
            ->assertJsonPath('data.0.role', 'Senior Software Engineer')
            ->assertJsonPath('data.0.responsibilities', ['Led API design'])
            ->assertJsonPath('meta.resolvedLocale', 'en');
    }

    public function test_experience_list_hides_invisible_experiences(): void
    {
        $visible = $this->createExperience('Visible Co', true, 1, '2022-01-01');
        ExperienceTranslation::query()->create([
            'experience_id' => $visible->id,
            'locale' => 'en',
            'role' => 'Visible Role',
        ]);

        $hidden = $this->createExperience('Hidden Co', false, 2, '2023-01-01');
        ExperienceTranslation::query()->create([
            'experience_id' => $hidden->id,
            'locale' => 'en',
            'role' => 'Hidden Role',
        ]);

        $response = $this->getJson('/api/v1/experience');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.company', 'Visible Co')
            ->assertJsonMissing(['company' => 'Hidden Co']);
    }

    public function test_experience_list_sorts_by_sort_order_then_start_date_desc(): void
    {
        $second = $this->createExperience('Second', true, null, '2023-01-01');
        $first = $this->createExperience('First', true, 1, '2020-01-01');
        $third = $this->createExperience('Third', true, null, '2021-01-01');

        foreach ([$first, $second, $third] as $experience) {
            ExperienceTranslation::query()->create([
                'experience_id' => $experience->id,
                'locale' => 'en',
                'role' => $experience->company.' Role',
            ]);
        }

        $response = $this->getJson('/api/v1/experience');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.company', 'First')
            ->assertJsonPath('data.1.company', 'Second')
            ->assertJsonPath('data.2.company', 'Third');
    }

    public function test_experience_list_uses_english_fallback_for_missing_french_fields(): void
    {
        $experience = $this->createExperience('Acme', true, 1, '2022-01-01');
        ExperienceTranslation::query()->create([
            'experience_id' => $experience->id,
            'locale' => 'en',
            'role' => 'Senior Software Engineer',
            'summary' => 'Built product platforms.',
            'responsibilities' => ['Led API design'],
        ]);
        ExperienceTranslation::query()->create([
            'experience_id' => $experience->id,
            'locale' => 'fr',
            'role' => 'Ingenieur logiciel senior',
        ]);

        $response = $this->getJson('/api/v1/experience?locale=fr');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.role', 'Ingenieur logiciel senior')
            ->assertJsonPath('data.0.summary', 'Built product platforms.')
            ->assertJsonPath('data.0.responsibilities', ['Led API design'])
            ->assertJsonPath('meta.resolvedLocale', 'fr')
            ->assertJsonPath('meta.fallbackUsed', true)
            ->assertJsonPath('meta.fallbackFields', ['experience.0.summary', 'experience.0.responsibilities']);
    }

    public function test_experience_list_rejects_invalid_locale(): void
    {
        $response = $this->getJson('/api/v1/experience?locale=de');

        $response
            ->assertStatus(422)
            ->assertJsonPath('data', null)
            ->assertJsonPath('errors.locale.0', 'The locale must be one of: en, fr.')
            ->assertJsonPath('meta.locale', 'en')
            ->assertJsonPath('meta.requestedLocale', 'de')
            ->assertJsonPath('meta.resolvedLocale', 'en')
            ->assertJsonPath('meta.fallbackUsed', true);
    }

    private function createExperience(string $company, bool $visible, ?int $sortOrder, string $startDate): Experience
    {
        return Experience::query()->create([
            'company' => $company,
            'company_url' => 'https://example.com',
            'location' => 'Remote',
            'start_date' => $startDate,
            'end_date' => null,
            'is_current' => true,
            'sort_order' => $sortOrder,
            'is_visible' => $visible,
        ]);
    }
}
