<?php

namespace App\Filament\Resources;

use App\Enums\ProjectStatus;
use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $recordTitleAttribute = 'slug';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Project')
                    ->tabs([
                        Tab::make('Content')
                            ->columns(2)
                            ->schema([
                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),

                                Select::make('status')
                                    ->options(ProjectStatus::options())
                                    ->default(ProjectStatus::Draft->value)
                                    ->required(),

                                DateTimePicker::make('published_at'),

                                Toggle::make('is_featured')
                                    ->label('Featured')
                                    ->default(false),

                                TextInput::make('sort_order')
                                    ->numeric()
                                    ->minValue(0),

                                TextInput::make('en_title')
                                    ->label('Title (English)')
                                    ->required()
                                    ->maxLength(255)
                                    ->dehydrated(),

                                Textarea::make('en_summary')
                                    ->label('Summary (English)')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->dehydrated(),

                                MarkdownEditor::make('en_content')
                                    ->label('Content (English)')
                                    ->columnSpanFull()
                                    ->dehydrated(),

                                TextInput::make('fr_title')
                                    ->label('Title (French)')
                                    ->maxLength(255)
                                    ->dehydrated(),

                                Textarea::make('fr_summary')
                                    ->label('Summary (French)')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->dehydrated(),

                                MarkdownEditor::make('fr_content')
                                    ->label('Content (French)')
                                    ->columnSpanFull()
                                    ->dehydrated(),
                            ]),

                        Tab::make('Media')
                            ->schema([
                                Select::make('featured_image_id')
                                    ->label('Featured image')
                                    ->relationship('featuredImage', 'path')
                                    ->searchable()
                                    ->preload(),

                                Select::make('seo_image_id')
                                    ->label('SEO image')
                                    ->relationship('seoImage', 'path')
                                    ->searchable()
                                    ->preload(),
                            ]),

                        Tab::make('SEO')
                            ->columns(2)
                            ->schema([
                                TextInput::make('en_seo_title')
                                    ->label('SEO title (English)')
                                    ->maxLength(255)
                                    ->dehydrated(),

                                TextInput::make('fr_seo_title')
                                    ->label('SEO title (French)')
                                    ->maxLength(255)
                                    ->dehydrated(),

                                Textarea::make('en_seo_description')
                                    ->label('SEO description (English)')
                                    ->rows(3)
                                    ->dehydrated(),

                                Textarea::make('fr_seo_description')
                                    ->label('SEO description (French)')
                                    ->rows(3)
                                    ->dehydrated(),

                                TagsInput::make('en_seo_keywords')
                                    ->label('SEO keywords (English)')
                                    ->dehydrated(),

                                TagsInput::make('fr_seo_keywords')
                                    ->label('SEO keywords (French)')
                                    ->dehydrated(),
                            ]),

                        Tab::make('Meta')
                            ->columns(2)
                            ->schema([
                                TextInput::make('uuid')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('created_by')
                                    ->label('Created by user ID')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('updated_by')
                                    ->label('Updated by user ID')
                                    ->disabled()
                                    ->dehydrated(false),

                                DateTimePicker::make('created_at')
                                    ->disabled()
                                    ->dehydrated(false),

                                DateTimePicker::make('updated_at')
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('featuredImage.path')
                    ->label('Image')
                    ->disk(fn (Project $record): ?string => $record->featuredImage?->disk)
                    ->square(),

                TextColumn::make('title')
                    ->label('Title')
                    ->state(fn (Project $record): ?string => $record->translation('en')?->title)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('translations', function (Builder $query) use ($search): void {
                            $query
                                ->where('locale', 'en')
                                ->where('title', 'ilike', "%{$search}%");
                        });
                    }),

                TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (ProjectStatus $state): string => str($state->value)->headline()->toString()),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),

                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(ProjectStatus::options()),

                TernaryFilter::make('is_featured')
                    ->label('Featured'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, array<string, mixed>>}
     */
    public static function splitProjectAndTranslationData(array $data): array
    {
        $translations = [
            'en' => [
                'title' => $data['en_title'] ?? null,
                'summary' => $data['en_summary'] ?? null,
                'content' => $data['en_content'] ?? null,
                'seo_title' => $data['en_seo_title'] ?? null,
                'seo_description' => $data['en_seo_description'] ?? null,
                'seo_keywords' => $data['en_seo_keywords'] ?? null,
            ],
            'fr' => [
                'title' => $data['fr_title'] ?? null,
                'summary' => $data['fr_summary'] ?? null,
                'content' => $data['fr_content'] ?? null,
                'seo_title' => $data['fr_seo_title'] ?? null,
                'seo_description' => $data['fr_seo_description'] ?? null,
                'seo_keywords' => $data['fr_seo_keywords'] ?? null,
            ],
        ];

        foreach ([
            'en_title',
            'en_summary',
            'en_content',
            'en_seo_title',
            'en_seo_description',
            'en_seo_keywords',
            'fr_title',
            'fr_summary',
            'fr_content',
            'fr_seo_title',
            'fr_seo_description',
            'fr_seo_keywords',
        ] as $key) {
            unset($data[$key]);
        }

        return [$data, $translations];
    }

    public static function syncTranslations(Project $project, array $translations): void
    {
        foreach ($translations as $locale => $translation) {
            if ($locale !== 'en' && ! self::hasTranslationContent($translation)) {
                $project->translations()->where('locale', $locale)->delete();

                continue;
            }

            $project->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title' => $translation['title'],
                    'summary' => $translation['summary'],
                    'content' => $translation['content'],
                    'seo_title' => $translation['seo_title'],
                    'seo_description' => $translation['seo_description'],
                    'seo_keywords' => $translation['seo_keywords'],
                ],
            );
        }
    }

    public static function addTranslationsToFormData(Project $project, array $data): array
    {
        $project->loadMissing('translations');

        foreach (['en', 'fr'] as $locale) {
            $translation = $project->translation($locale);
            $prefix = $locale.'_';

            $data[$prefix.'title'] = $translation?->title;
            $data[$prefix.'summary'] = $translation?->summary;
            $data[$prefix.'content'] = $translation?->content;
            $data[$prefix.'seo_title'] = $translation?->seo_title;
            $data[$prefix.'seo_description'] = $translation?->seo_description;
            $data[$prefix.'seo_keywords'] = $translation?->seo_keywords;
        }

        return $data;
    }

    private static function hasTranslationContent(array $translation): bool
    {
        return collect($translation)
            ->flatten()
            ->filter(fn (mixed $value): bool => filled($value))
            ->isNotEmpty();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
