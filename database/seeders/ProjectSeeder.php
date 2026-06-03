<?php

namespace Database\Seeders;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\ProjectTranslation;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->projects() as $index => $data) {
            $project = Project::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'status' => ProjectStatus::Published,
                    'featured_image_id' => null,
                    'seo_image_id' => null,
                    'is_featured' => true,
                    'sort_order' => $index + 1,
                    'published_at' => now(),
                ],
            );

            foreach ($data['translations'] as $locale => $translation) {
                ProjectTranslation::query()->updateOrCreate(
                    [
                        'project_id' => $project->id,
                        'locale' => $locale,
                    ],
                    $translation,
                );
            }
        }
    }

    /**
     * @return array<int, array{slug: string, translations: array<string, array<string, mixed>>}>
     */
    private function projects(): array
    {
        return [
            [
                'slug' => 'nam-house-of-sleep',
                'translations' => [
                    'en' => [
                        'title' => 'Nam House of Sleep',
                        'summary' => 'CMS-managed product website for a sleep and home product brand.',
                        'content' => <<<'MARKDOWN'
Informative website with CMS-managed product pages and a modern product-focused frontend for presenting collections clearly.

Type: Product website
Role: Full-stack engineer
Stack: React, ExpressJS, CMS, Product UI
Categories: CMS, Backend

## Problem
The site needs to present product and brand information clearly while staying easy to update through CMS-managed content.

- Product pages should be editable without changing frontend code.
- Collections and content sections need predictable structure for future CMS fields.

## Architecture
A React frontend consumes structured product and page data from an ExpressJS layer prepared for CMS integration.

- Reusable frontend sections for product collections, content blocks, and calls to action.
- Backend API boundary designed around product, collection, and page models.

## Stack Decisions
React and ExpressJS keep the implementation lightweight while leaving room for a CMS-backed product catalog.

- React supports a modern product-focused interface.
- ExpressJS keeps API and content delivery simple for the current scope.

## Challenges
The main constraint is balancing flexible CMS editing with a polished product presentation layer.

- Avoiding overly rigid product templates.
- Keeping frontend components reusable across product and content pages.

## Security Considerations
CMS-managed product content should be treated as untrusted input and rendered safely.

- Validate and sanitize rich text fields before rendering.
- Keep admin editing and public content delivery behind separate authorization boundaries.

## Scalability Thoughts
The product model can grow from simple pages into collections, categories, and richer media assets.

- Cache public product content where possible.
- Keep image and gallery fields structured for CDN-backed delivery later.

## Outcomes
- Modern product-focused frontend foundation.
- CMS-ready structure for product and content updates.

## Future Improvements
- Add final product screenshots and gallery assets.
- Connect page metadata to CMS SEO fields.
MARKDOWN,
                        'seo_title' => 'Nam House of Sleep - Afif El Charif',
                        'seo_description' => 'CMS-managed product website for a sleep and home product brand.',
                        'seo_keywords' => ['CMS', 'Backend', 'React', 'ExpressJS', 'Product UI'],
                    ],
                    'fr' => [
                        'title' => 'Nam House of Sleep',
                        'summary' => 'Site produit gere par CMS pour une marque autour du sommeil et de la maison.',
                        'content' => 'Site informatif avec pages produit gerees par CMS et frontend moderne centre sur la presentation claire des collections.',
                        'seo_title' => 'Nam House of Sleep - Afif El Charif',
                        'seo_description' => 'Site produit gere par CMS pour une marque autour du sommeil et de la maison.',
                        'seo_keywords' => ['CMS', 'Backend'],
                    ],
                ],
            ],
            [
                'slug' => 'ai-sourcing-platform',
                'translations' => [
                    'en' => [
                        'title' => 'AI Sourcing Platform',
                        'summary' => 'AI-powered sourcing and workflow automation platform.',
                        'content' => <<<'MARKDOWN'
AI-powered sourcing and workflow automation platform focused on scalable architecture and intelligent operational flows.

Type: AI platform
Role: Full-stack engineer
Stack: AI Workflows, Automation, Scalable APIs, Sourcing
Categories: AI, Backend

## Problem
Sourcing workflows involve repeated research, extraction, ranking, and follow-up tasks that benefit from structured automation.

- Users need AI assistance without losing control over final decisions.
- The system needs to keep workflow state visible and auditable.

## Architecture
The platform is modeled around sourcing pipelines, queued AI tasks, and API boundaries for operational workflow steps.

- Pipeline data model for candidates, sources, tasks, and statuses.
- Async processing layer for extraction, summarization, and ranking.

## Stack Decisions
The architecture prioritizes clear backend contracts and isolated AI workflow execution.

- API-first design keeps AI features separate from the user interface.
- Queues provide resilience for slower AI and enrichment tasks.

## Challenges
The hardest part is making AI output useful, reviewable, and safe for operational decisions.

- Avoiding opaque automation for important sourcing steps.
- Designing fallbacks for incomplete or low-confidence AI output.

## Security Considerations
Sourcing data and AI prompts can contain sensitive business context that must be scoped carefully.

- Separate user-owned data from shared system prompts.
- Log AI actions without exposing unnecessary sensitive details.

## Scalability Thoughts
The workflow model should support more sources, more enrichment steps, and larger background workloads over time.

- Queue AI tasks independently from request/response paths.
- Use explicit status transitions for long-running sourcing jobs.

## Outcomes
- Scalable foundation for intelligent sourcing workflows.
- Clear separation between automation logic, API contracts, and user-facing flows.

## Future Improvements
- Add confidence scoring and approval workflows.
- Add integrations for external sourcing data providers.
MARKDOWN,
                        'seo_title' => 'AI Sourcing Platform - Afif El Charif',
                        'seo_description' => 'AI-powered sourcing and workflow automation platform focused on scalable architecture and intelligent operational flows.',
                        'seo_keywords' => ['AI', 'Backend', 'Automation', 'Sourcing', 'Scalable APIs'],
                    ],
                    'fr' => [
                        'title' => 'AI Sourcing Platform',
                        'summary' => "Plateforme de sourcing et d'automatisation de workflows alimentee par l'IA.",
                        'content' => "Plateforme de sourcing et d'automatisation par IA, concue autour d'une architecture scalable et de workflows operationnels intelligents.",
                        'seo_title' => 'AI Sourcing Platform - Afif El Charif',
                        'seo_description' => "Plateforme de sourcing et d'automatisation par IA, concue autour d'une architecture scalable et de workflows operationnels intelligents.",
                        'seo_keywords' => ['AI', 'Backend'],
                    ],
                ],
            ],
            [
                'slug' => 'household-manager-app',
                'translations' => [
                    'en' => [
                        'title' => 'Household Manager App',
                        'summary' => 'Realtime household collaboration app for pantry, groceries, and recipes.',
                        'content' => <<<'MARKDOWN'
Mobile app for pantry management, grocery lists, recipes, and realtime household collaboration across shared accounts.

Type: Mobile application
Role: Mobile and backend engineer
Stack: React Native, Laravel, Firebase, Realtime Sync
Categories: Mobile, Backend

## Problem
Household planning data changes frequently and often needs to be shared across multiple users in realtime.

- Pantry, grocery, and recipe flows need to remain simple on mobile.
- Shared household state needs predictable synchronization.

## Architecture
The app combines a React Native client, Laravel domain API, and Firebase realtime layer for collaborative updates.

- React Native handles mobile-first workflows and offline-friendly screens.
- Laravel owns household accounts, pantry models, recipes, and grocery logic.
- Firebase supports realtime shared list and household state updates.

## Stack Decisions
The stack separates domain logic from realtime sync while keeping the mobile experience responsive.

- Laravel is a strong fit for structured domain rules and APIs.
- Firebase is useful for live collaboration and fast shared-state updates.

## Challenges
The main challenge is keeping shared data accurate while preserving a simple mobile UX.

- Avoiding conflicts when multiple household members update the same lists.
- Keeping recipes, pantry items, and grocery suggestions connected without clutter.

## Security Considerations
Household data should be scoped tightly to members and protected across API and realtime layers.

- Enforce household-level authorization on backend resources.
- Mirror access rules in Firebase security configuration.

## Scalability Thoughts
The system should support more households, larger item histories, and AI features without slowing core workflows.

- Keep AI parsing and recommendation work asynchronous.
- Archive or summarize historical pantry and shopping events over time.

## Outcomes
- Mobile-first foundation for household collaboration.
- Realtime architecture direction for shared lists and pantry updates.

## Future Improvements
- Add receipt image parsing and ingredient normalization.
- Add smarter shopping optimization based on usage patterns.
MARKDOWN,
                        'seo_title' => 'Household Manager App - Afif El Charif',
                        'seo_description' => 'Mobile app for pantry management, grocery lists, recipes, and realtime household collaboration across shared accounts.',
                        'seo_keywords' => ['Mobile', 'Backend', 'React Native', 'Laravel', 'Firebase'],
                    ],
                    'fr' => [
                        'title' => 'Household Manager App',
                        'summary' => 'Application collaborative en temps reel pour garde-manger, courses et recettes.',
                        'content' => 'Application mobile pour gerer le garde-manger, les listes de courses, les recettes et la collaboration familiale en temps reel.',
                        'seo_title' => 'Household Manager App - Afif El Charif',
                        'seo_description' => 'Application mobile pour gerer le garde-manger, les listes de courses, les recettes et la collaboration familiale en temps reel.',
                        'seo_keywords' => ['Mobile', 'Backend'],
                    ],
                ],
            ],
        ];
    }
}
