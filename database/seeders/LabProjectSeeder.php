<?php

namespace Database\Seeders;

use App\Enums\LabProjectStatus;
use App\Models\LabProject;
use App\Models\LabProjectTranslation;
use Illuminate\Database\Seeder;

class LabProjectSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->labProjects() as $index => $data) {
            $labProject = LabProject::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'status' => LabProjectStatus::Building,
                    'featured_image_id' => null,
                    'seo_image_id' => null,
                    'is_featured' => true,
                    'sort_order' => $index + 1,
                    'started_at' => null,
                    'published_at' => now(),
                ],
            );

            LabProjectTranslation::query()->updateOrCreate(
                [
                    'lab_project_id' => $labProject->id,
                    'locale' => 'en',
                ],
                [
                    'title' => $data['title'],
                    'summary' => $data['summary'],
                    'content' => $data['content'],
                    'problem' => $data['problem'],
                    'approach' => $data['approach'],
                    'architecture_notes' => $data['architecture_notes'],
                    'seo_title' => $data['title'].' - Afif El Charif',
                    'seo_description' => $data['summary'],
                    'seo_keywords' => $data['seo_keywords'],
                ],
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function labProjects(): array
    {
        return [
            [
                'slug' => 'ai-crm-assistant',
                'title' => 'AI CRM Assistant',
                'summary' => 'Extracts follow-ups, reminders, summaries, and contacts from meetings or notes to support cleaner relationship management workflows.',
                'content' => <<<'MARKDOWN'
A lightweight assistant that turns unstructured meeting notes into follow-ups, reminders, contact updates, and concise relationship summaries.

Type: AI workflow lab
Showcase: AI workflows, queues, and automation
Stack: AI Workflows, Queues, Automation, CRM

## Skills Demonstrated
- AI extraction workflows
- Queue-backed automation
- CRM data modeling

## Planned Architecture
- Laravel API for contacts, notes, reminders, and activity history
- Queued AI processing for summaries and follow-up extraction
- React interface for reviewing and approving suggested actions

## Why It Matters
It demonstrates how AI can support daily business workflows without replacing human review or control.
MARKDOWN,
                'problem' => 'Meeting notes often contain useful actions, but they are easy to lose without a structured follow-up workflow.',
                'approach' => 'Use a backend workflow pipeline for note ingestion, queued AI extraction, and user-approved CRM updates.',
                'architecture_notes' => $this->notes([
                    ['Problem', 'Meeting notes often contain useful actions, but they are easy to lose without a structured follow-up workflow.', ['Extract contacts, reminders, summaries, and next steps from messy input.', 'Keep AI suggestions reviewable before they affect CRM data.']],
                    ['Architecture', 'The system uses a backend workflow pipeline for note ingestion, queued AI extraction, and user-approved CRM updates.', ['Store raw notes and normalized extraction results separately.', 'Queue AI processing to avoid blocking the main user flow.']],
                    ['Stack Decisions', 'Laravel, queues, and a focused React interface keep the workflow practical and inspectable.', ['Laravel models contacts, notes, tasks, and reminders.', 'Queue workers isolate slower AI operations from the UI.']],
                    ['Challenges', 'The key challenge is extracting useful actions without creating noisy or inaccurate CRM updates.', ['Handle vague meeting notes and incomplete contact references.', 'Avoid over-automation by keeping human approval in the loop.']],
                    ['Security Considerations', 'CRM notes can contain sensitive business context and should be scoped carefully.', ['Limit note visibility to the owning user or workspace.', 'Avoid logging full private note content unnecessarily.']],
                    ['Scalability Thoughts', 'Extraction jobs should scale independently as note volume grows.', ['Batch background processing where possible.', 'Track extraction status and retry failed AI jobs safely.']],
                    ['Future Improvements', 'Future versions can add calendar sync, contact enrichment, and confidence scoring.', ['Add confidence indicators for suggested follow-ups.', 'Connect approved reminders to calendar or task systems.']],
                ]),
                'seo_keywords' => ['AI Workflows', 'Queues', 'Automation', 'CRM'],
            ],
            [
                'slug' => 'deployment-dashboard',
                'title' => 'Deployment Dashboard',
                'summary' => 'Tracks DigitalOcean deployments, uptime, SSL status, logs, and services from a focused operations dashboard.',
                'content' => <<<'MARKDOWN'
A compact operations dashboard for tracking deployments, uptime, SSL health, service status, logs, and backend infrastructure signals.

Type: DevOps lab
Showcase: DevOps, monitoring, and backend integrations
Stack: DigitalOcean, Monitoring, Logs, Backend APIs

## Skills Demonstrated
- DevOps integrations
- Monitoring workflows
- Backend service orchestration

## Planned Architecture
- Backend integrations with DigitalOcean APIs and service metadata
- Scheduled checks for uptime, SSL expiry, and deployment state
- Dashboard views for logs, services, incidents, and environment health

## Why It Matters
It shows practical infrastructure visibility for production systems without introducing heavyweight monitoring complexity.
MARKDOWN,
                'problem' => 'Small production systems still need clear visibility into deployments, uptime, certificates, services, and logs.',
                'approach' => 'Combine provider integrations, scheduled health checks, and compact operational views.',
                'architecture_notes' => $this->notes([
                    ['Problem', 'Small production systems still need clear visibility into deployments, uptime, certificates, services, and logs.', ['Centralize operational signals that are usually scattered across providers.', 'Surface issues before they become user-facing incidents.']],
                    ['Architecture', 'The dashboard combines provider integrations, scheduled health checks, and compact operational views.', ['Poll DigitalOcean and related service APIs for infrastructure state.', 'Store health-check snapshots for uptime and SSL reporting.']],
                    ['Stack Decisions', 'A backend-first design is appropriate because most value comes from integrations and scheduled checks.', ['Use backend jobs for recurring checks and API polling.', 'Keep frontend views focused on status, incidents, and logs.']],
                    ['Challenges', 'The challenge is presenting operational detail without building an overly complex monitoring product.', ['Avoid alert fatigue and noisy status surfaces.', 'Keep logs and health data searchable but lightweight.']],
                    ['Security Considerations', 'Provider credentials and deployment data require careful handling.', ['Store provider tokens server-side only.', 'Avoid exposing sensitive environment or log values to the client.']],
                    ['Scalability Thoughts', 'Checks and integrations should scale with the number of monitored services.', ['Run checks asynchronously and store compact status history.', 'Separate incident state from raw log collection.']],
                    ['Future Improvements', 'Future versions can add alert routing, incident timelines, and deployment comparisons.', ['Add webhook notifications for downtime and SSL expiry.', 'Add service-level incident history and deployment markers.']],
                ]),
                'seo_keywords' => ['DigitalOcean', 'Monitoring', 'Logs', 'Backend APIs'],
            ],
            [
                'slug' => 'smart-pantry-ai-engine',
                'title' => 'Smart Pantry AI Engine',
                'summary' => 'AI recipe suggestions, receipt parsing, expiration tracking, and shopping optimization for household planning flows.',
                'content' => <<<'MARKDOWN'
An AI-assisted household engine for recipe suggestions, receipt parsing, expiration tracking, and smarter shopping decisions.

Type: Mobile AI lab
Showcase: React Native, Laravel, Firebase, and AI features
Stack: React Native, Laravel, Firebase, AI Features

## Skills Demonstrated
- React Native product flows
- Laravel domain modeling
- Firebase realtime collaboration
- AI-assisted recommendations

## Planned Architecture
- React Native app for pantry, grocery, recipe, and household workflows
- Laravel API for accounts, pantry items, recipes, and shopping logic
- Firebase realtime layer for shared household state
- AI services for parsing receipts and generating recipe suggestions

## Why It Matters
It connects mobile UX, household collaboration, and AI features around a real everyday workflow.
MARKDOWN,
                'problem' => 'Household food planning involves changing pantry state, shared lists, recipes, and shopping decisions across multiple people.',
                'approach' => 'Combine mobile workflows, backend domain models, realtime collaboration, and asynchronous AI features.',
                'architecture_notes' => $this->notes([
                    ['Problem', 'Household food planning involves changing pantry state, shared lists, recipes, and shopping decisions across multiple people.', ['Keep household members aligned on what exists and what needs buying.', 'Use AI to reduce manual entry without making the workflow feel complex.']],
                    ['Architecture', 'The system combines mobile workflows, backend domain models, realtime collaboration, and asynchronous AI features.', ['React Native provides pantry, recipe, receipt, and shopping screens.', 'Laravel models households, items, recipes, and shopping rules.', 'Firebase supports realtime shared household state.']],
                    ['Stack Decisions', "React Native, Laravel, Firebase, and AI services map cleanly to the product's mobile, domain, realtime, and intelligence layers.", ['Laravel keeps business rules explicit and testable.', 'Firebase handles collaborative updates with low perceived latency.']],
                    ['Challenges', 'The hardest part is keeping AI suggestions useful while preserving trust in household inventory data.', ['Normalize receipt items into usable pantry entities.', 'Avoid recipe suggestions that ignore expiration dates or preferences.']],
                    ['Security Considerations', 'Household data should only be visible to authorized members.', ['Apply household membership checks across API endpoints.', 'Mirror access rules in realtime database permissions.']],
                    ['Scalability Thoughts', 'The AI layer should scale separately from core mobile and household flows.', ['Run receipt parsing and recipe generation asynchronously.', 'Cache common suggestions and normalized ingredients where possible.']],
                    ['Future Improvements', 'Future versions can add dietary profiles, household analytics, and smarter shopping optimization.', ['Add preference-aware recipe recommendations.', 'Add shopping optimization based on usage and expiry patterns.']],
                ]),
                'seo_keywords' => ['React Native', 'Laravel', 'Firebase', 'AI Features'],
            ],
        ];
    }

    /**
     * @param  array<int, array{0: string, 1: string, 2: array<int, string>}>  $sections
     */
    private function notes(array $sections): string
    {
        return collect($sections)
            ->map(function (array $section): string {
                $items = collect($section[2])
                    ->map(fn (string $item): string => '- '.$item)
                    ->implode("\n");

                return "## {$section[0]}\n{$section[1]}\n\n{$items}";
            })
            ->implode("\n\n");
    }
}
