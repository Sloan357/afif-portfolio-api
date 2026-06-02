<?php

namespace Tests\Unit;

use App\Http\Resources\Api\V1\ExperienceResource;
use App\Models\Experience;
use App\Models\ExperienceTranslation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Tests\TestCase;

class ExperienceResourceTest extends TestCase
{
    public function test_experience_resource_serializes_public_shape(): void
    {
        $experience = $this->makeExperience();
        $experience->setRelation('translations', new Collection([
            new ExperienceTranslation([
                'locale' => 'en',
                'role' => 'Senior Software Engineer',
                'summary' => 'Built product platforms.',
                'responsibilities' => ['Led API design', 'Improved delivery workflows'],
            ]),
        ]));

        $request = Request::create('/api/v1/experience', 'GET');
        $resource = new ExperienceResource($experience);
        $data = $resource->resolve($request);
        $meta = $resource->fallbackMeta($request);

        $this->assertSame('11111111-1111-1111-1111-111111111111', $data['uuid']);
        $this->assertSame('Acme', $data['company']);
        $this->assertSame('https://acme.example.com', $data['companyUrl']);
        $this->assertSame('Remote', $data['location']);
        $this->assertSame('Senior Software Engineer', $data['role']);
        $this->assertSame('Built product platforms.', $data['summary']);
        $this->assertSame(['Led API design', 'Improved delivery workflows'], $data['responsibilities']);
        $this->assertSame('2022-01-01', $data['startDate']);
        $this->assertNull($data['endDate']);
        $this->assertTrue($data['isCurrent']);
        $this->assertSame(1, $data['sortOrder']);
        $this->assertFalse($meta['fallbackUsed']);
        $this->assertSame([], $meta['missingFields']);
        $this->assertSame([], $meta['fallbackFields']);
        $this->assertArrayNotHasKey('id', $data);
        $this->assertArrayNotHasKey('isVisible', $data);
        $this->assertArrayNotHasKey('createdBy', $data);
        $this->assertArrayNotHasKey('updatedBy', $data);
    }

    public function test_experience_resource_uses_english_fallback_for_missing_french_fields(): void
    {
        $experience = $this->makeExperience();
        $experience->setRelation('translations', new Collection([
            new ExperienceTranslation([
                'locale' => 'en',
                'role' => 'Senior Software Engineer',
                'summary' => 'Built product platforms.',
                'responsibilities' => ['Led API design'],
            ]),
            new ExperienceTranslation([
                'locale' => 'fr',
                'role' => 'Ingenieur logiciel senior',
            ]),
        ]));

        $request = Request::create('/api/v1/experience', 'GET', ['locale' => 'fr']);
        $resource = new ExperienceResource($experience);
        $data = $resource->resolve($request);
        $meta = $resource->fallbackMeta($request);

        $this->assertSame('Ingenieur logiciel senior', $data['role']);
        $this->assertSame('Built product platforms.', $data['summary']);
        $this->assertSame(['Led API design'], $data['responsibilities']);
        $this->assertTrue($meta['fallbackUsed']);
        $this->assertSame([], $meta['missingFields']);
        $this->assertSame(['summary', 'responsibilities'], $meta['fallbackFields']);
    }

    public function test_experience_resource_reports_missing_localized_fields(): void
    {
        $experience = $this->makeExperience();
        $experience->setRelation('translations', new Collection([
            new ExperienceTranslation([
                'locale' => 'en',
                'role' => 'Senior Software Engineer',
                'responsibilities' => [],
            ]),
        ]));

        $request = Request::create('/api/v1/experience', 'GET');
        $resource = new ExperienceResource($experience);
        $data = $resource->resolve($request);
        $meta = $resource->fallbackMeta($request);

        $this->assertSame('Senior Software Engineer', $data['role']);
        $this->assertNull($data['summary']);
        $this->assertSame([], $data['responsibilities']);
        $this->assertFalse($meta['fallbackUsed']);
        $this->assertSame(['summary', 'responsibilities'], $meta['missingFields']);
        $this->assertSame([], $meta['fallbackFields']);
    }

    private function makeExperience(): Experience
    {
        return new Experience([
            'uuid' => '11111111-1111-1111-1111-111111111111',
            'company' => 'Acme',
            'company_url' => 'https://acme.example.com',
            'location' => 'Remote',
            'start_date' => '2022-01-01',
            'end_date' => null,
            'is_current' => true,
            'sort_order' => 1,
            'is_visible' => true,
        ]);
    }
}
