<?php

namespace Database\Seeders;

use App\Models\Experience;
use App\Models\ExperienceTranslation;
use Illuminate\Database\Seeder;

class ExperienceSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->experiences() as $index => $data) {
            $experience = Experience::query()->updateOrCreate(
                [
                    'company' => $data['company'],
                    'start_date' => $data['start_date'],
                ],
                [
                    'company_url' => $data['company_url'],
                    'location' => null,
                    'end_date' => $data['end_date'],
                    'is_current' => $data['is_current'],
                    'sort_order' => $index + 1,
                    'is_visible' => true,
                ],
            );

            ExperienceTranslation::query()->updateOrCreate(
                [
                    'experience_id' => $experience->id,
                    'locale' => 'en',
                ],
                [
                    'role' => $data['role'],
                    'summary' => $data['summary'],
                    'responsibilities' => $data['responsibilities'],
                ],
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function experiences(): array
    {
        return [
            [
                'role' => 'Freelance Software Engineer',
                'company' => 'Freelance',
                'company_url' => null,
                'start_date' => '2023-01-01',
                'end_date' => null,
                'is_current' => true,
                'summary' => 'Designed and delivered SaaS platforms, CMS-backed websites, mobile applications, and backend systems for product-focused teams.',
                'responsibilities' => [
                    'Designed SaaS platforms and mobile applications',
                    'Built React Native and Laravel systems',
                    'Integrated AI features using OpenAI APIs',
                    'Managed DigitalOcean deployments',
                    'Developed CMS systems and backend architecture',
                ],
            ],
            [
                'role' => 'Software Engineer',
                'company' => 'Coddict',
                'company_url' => null,
                'start_date' => '2019-01-01',
                'end_date' => '2023-12-31',
                'is_current' => false,
                'summary' => 'Worked on PHP and Symfony backend systems for SaaS products serving accounting, tax, and operational workflows.',
                'responsibilities' => [
                    'Developed Symfony and PHP backend services',
                    'Built SaaS systems for accounting and tax operations',
                    'Designed and maintained REST APIs',
                    'Improved backend performance',
                    'Worked on microservices and CMS-like systems',
                ],
            ],
            [
                'role' => 'Solutions Engineer',
                'company' => 'FUJIFILM Synapse',
                'company_url' => null,
                'start_date' => '2015-01-01',
                'end_date' => '2019-12-31',
                'is_current' => false,
                'summary' => 'Supported medical imaging systems and technical integrations in PACS/RIS environments with a focus on reliability and troubleshooting.',
                'responsibilities' => [
                    'Supported medical systems and engineering workflows',
                    'Worked across PACS/RIS environments',
                    'Handled technical integrations and troubleshooting',
                ],
            ],
        ];
    }
}
