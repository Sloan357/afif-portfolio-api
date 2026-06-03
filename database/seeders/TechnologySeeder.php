<?php

namespace Database\Seeders;

use App\Enums\TechnologyCategory;
use App\Models\Technology;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TechnologySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->technologies() as $index => $technology) {
            Technology::query()->updateOrCreate(
                ['slug' => $technology['slug']],
                [
                    'name' => $technology['name'],
                    'category' => $technology['category'],
                    'website_url' => $technology['website_url'] ?? null,
                    'icon_media_id' => null,
                    'color' => null,
                    'sort_order' => $index + 1,
                    'is_visible' => true,
                ],
            );
        }
    }

    /**
     * @return array<int, array{name: string, slug: string, category: TechnologyCategory, website_url?: string|null}>
     */
    private function technologies(): array
    {
        $items = [
            ['Laravel', TechnologyCategory::Framework, 'https://laravel.com'],
            ['Symfony', TechnologyCategory::Framework, 'https://symfony.com'],
            ['PHP', TechnologyCategory::Language, 'https://www.php.net'],
            ['React', TechnologyCategory::Library, 'https://react.dev'],
            ['Next.js', TechnologyCategory::Framework, 'https://nextjs.org'],
            ['React Native', TechnologyCategory::Framework, 'https://reactnative.dev'],
            ['Firebase', TechnologyCategory::Platform, 'https://firebase.google.com'],
            ['DigitalOcean', TechnologyCategory::Cloud, 'https://www.digitalocean.com'],
            ['OpenAI APIs', TechnologyCategory::Platform, 'https://openai.com/api'],
            ['REST APIs', TechnologyCategory::Other],
            ['CMS', TechnologyCategory::Cms],
            ['SaaS', TechnologyCategory::Other],
            ['SaaS systems', TechnologyCategory::Other],
            ['AI integrations', TechnologyCategory::Platform],
            ['AI Workflows', TechnologyCategory::Platform],
            ['AI Features', TechnologyCategory::Platform],
            ['Automation', TechnologyCategory::Tool],
            ['Queues', TechnologyCategory::Tool],
            ['Backend architecture', TechnologyCategory::Other],
            ['Backend APIs', TechnologyCategory::Other],
            ['Scalable APIs', TechnologyCategory::Other],
            ['Product UI', TechnologyCategory::Design],
            ['ExpressJS', TechnologyCategory::Framework, 'https://expressjs.com'],
            ['Microservices', TechnologyCategory::Other],
            ['Realtime Sync', TechnologyCategory::Other],
            ['Monitoring', TechnologyCategory::Tool],
            ['Logs', TechnologyCategory::Tool],
            ['DevOps', TechnologyCategory::Tool],
            ['CRM', TechnologyCategory::Other],
            ['Sourcing', TechnologyCategory::Other],
            ['PACS', TechnologyCategory::Other],
            ['RIS', TechnologyCategory::Other],
            ['Medical Systems', TechnologyCategory::Other],
            ['Integrations', TechnologyCategory::Other],
            ['Support Engineering', TechnologyCategory::Other],
        ];

        return array_map(fn (array $item): array => [
            'name' => $item[0],
            'slug' => Str::slug($item[0]),
            'category' => $item[1],
            'website_url' => $item[2] ?? null,
        ], $items);
    }
}
