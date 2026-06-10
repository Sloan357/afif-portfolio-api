<?php

namespace App\Filament\Resources;

use App\Enums\HeroContentStatus;
use App\Filament\Resources\HeroContentResource\Pages;
use App\Models\HeroContent;
use Closure;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
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

class HeroContentResource extends Resource
{
    protected static ?string $model = HeroContent::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $recordTitleAttribute = 'key';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Hero content')
                    ->tabs([
                        Tab::make('Content')
                            ->columns(2)
                            ->schema([
                                TextInput::make('key')
                                    ->default('homepage')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),

                                Select::make('status')
                                    ->options(HeroContentStatus::options())
                                    ->default(HeroContentStatus::Draft->value)
                                    ->required(),

                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(false),

                                DateTimePicker::make('published_at'),

                                TextInput::make('sort_order')
                                    ->numeric()
                                    ->minValue(0),

                                TextInput::make('en_badge')
                                    ->label('Badge (English)')
                                    ->maxLength(255)
                                    ->dehydrated(),

                                TextInput::make('en_headline')
                                    ->label('Headline (English)')
                                    ->required()
                                    ->maxLength(255)
                                    ->dehydrated(),

                                MarkdownEditor::make('en_description')
                                    ->label('Description (English)')
                                    ->columnSpanFull()
                                    ->dehydrated(),

                                TextInput::make('fr_badge')
                                    ->label('Badge (French)')
                                    ->maxLength(255)
                                    ->dehydrated(),

                                TextInput::make('fr_headline')
                                    ->label('Headline (French)')
                                    ->maxLength(255)
                                    ->dehydrated(),

                                MarkdownEditor::make('fr_description')
                                    ->label('Description (French)')
                                    ->columnSpanFull()
                                    ->dehydrated(),
                            ]),

                        Tab::make('CTAs')
                            ->columns(2)
                            ->schema([
                                TextInput::make('primary_cta_url')
                                    ->label('Primary CTA URL')
                                    ->maxLength(255)
                                    ->rule(static::ctaUrlRule())
                                    ->placeholder('#projects'),

                                TextInput::make('secondary_cta_url')
                                    ->label('Secondary CTA URL')
                                    ->maxLength(255)
                                    ->rule(static::ctaUrlRule())
                                    ->placeholder('#contact'),

                                TextInput::make('en_primary_cta_label')
                                    ->label('Primary CTA label (English)')
                                    ->maxLength(255)
                                    ->dehydrated(),

                                TextInput::make('fr_primary_cta_label')
                                    ->label('Primary CTA label (French)')
                                    ->maxLength(255)
                                    ->dehydrated(),

                                TextInput::make('en_secondary_cta_label')
                                    ->label('Secondary CTA label (English)')
                                    ->maxLength(255)
                                    ->dehydrated(),

                                TextInput::make('fr_secondary_cta_label')
                                    ->label('Secondary CTA label (French)')
                                    ->maxLength(255)
                                    ->dehydrated(),
                            ]),

                        Tab::make('Capabilities')
                            ->schema([
                                Repeater::make('en_capabilities')
                                    ->label('Capabilities (English)')
                                    ->simple(TextInput::make('label')->maxLength(255))
                                    ->dehydrated(),

                                Repeater::make('fr_capabilities')
                                    ->label('Capabilities (French)')
                                    ->simple(TextInput::make('label')->maxLength(255))
                                    ->dehydrated(),
                            ]),

                        Tab::make('Architecture')
                            ->schema([
                                Repeater::make('en_architecture_items')
                                    ->label('Architecture items (English)')
                                    ->schema([
                                        TextInput::make('title')
                                            ->required()
                                            ->maxLength(255),

                                        Textarea::make('description')
                                            ->rows(3),
                                    ])
                                    ->dehydrated(),

                                Repeater::make('fr_architecture_items')
                                    ->label('Architecture items (French)')
                                    ->schema([
                                        TextInput::make('title')
                                            ->maxLength(255),

                                        Textarea::make('description')
                                            ->rows(3),
                                    ])
                                    ->dehydrated(),
                            ]),

                        Tab::make('Media')
                            ->schema([
                                Select::make('hero_image_id')
                                    ->label('Hero image')
                                    ->relationship('heroImage', 'path')
                                    ->searchable()
                                    ->preload(),

                                Select::make('og_image_id')
                                    ->label('OG image')
                                    ->relationship('ogImage', 'path')
                                    ->searchable()
                                    ->preload(),
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

    private static function ctaUrlRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if ($value === null || $value === '') {
                return;
            }

            if (! is_string($value) || preg_match('/\s|[\x00-\x1F\x7F]/', $value)) {
                $fail('The :attribute must be a valid URL, relative path, or anchor.');

                return;
            }

            if (preg_match('/^#[A-Za-z0-9][A-Za-z0-9_-]*$/', $value)) {
                return;
            }

            if (preg_match('/^\/(?!\/)[A-Za-z0-9._~\/-]*(?:#[A-Za-z0-9][A-Za-z0-9_-]*)?$/', $value)) {
                return;
            }

            if (filter_var($value, FILTER_VALIDATE_URL) !== false) {
                $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));

                if (in_array($scheme, ['http', 'https'], true)) {
                    return;
                }
            }

            $fail('The :attribute must be a valid URL, relative path, or anchor.');
        };
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('heroImage.path')
                    ->label('Image')
                    ->disk(fn (HeroContent $record): ?string => $record->heroImage?->disk)
                    ->square(),

                TextColumn::make('key')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('headline')
                    ->label('Headline')
                    ->state(fn (HeroContent $record): ?string => $record->translation('en')?->headline)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('translations', function (Builder $query) use ($search): void {
                            $query
                                ->where('locale', 'en')
                                ->where('headline', 'ilike', "%{$search}%");
                        });
                    }),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (HeroContentStatus $state): string => str($state->value)->headline()->toString()),

                IconColumn::make('is_active')
                    ->label('Active')
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
                    ->options(HeroContentStatus::options()),

                TernaryFilter::make('is_active')
                    ->label('Active'),
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
    public static function splitHeroContentAndTranslationData(array $data): array
    {
        $translations = [
            'en' => [
                'badge' => $data['en_badge'] ?? null,
                'headline' => $data['en_headline'] ?? null,
                'description' => $data['en_description'] ?? null,
                'primary_cta_label' => $data['en_primary_cta_label'] ?? null,
                'secondary_cta_label' => $data['en_secondary_cta_label'] ?? null,
                'capabilities' => self::cleanList($data['en_capabilities'] ?? null),
                'architecture_items' => self::cleanArchitectureItems($data['en_architecture_items'] ?? null),
            ],
            'fr' => [
                'badge' => $data['fr_badge'] ?? null,
                'headline' => $data['fr_headline'] ?? null,
                'description' => $data['fr_description'] ?? null,
                'primary_cta_label' => $data['fr_primary_cta_label'] ?? null,
                'secondary_cta_label' => $data['fr_secondary_cta_label'] ?? null,
                'capabilities' => self::cleanList($data['fr_capabilities'] ?? null),
                'architecture_items' => self::cleanArchitectureItems($data['fr_architecture_items'] ?? null),
            ],
        ];

        foreach ([
            'en_badge',
            'en_headline',
            'en_description',
            'en_primary_cta_label',
            'en_secondary_cta_label',
            'en_capabilities',
            'en_architecture_items',
            'fr_badge',
            'fr_headline',
            'fr_description',
            'fr_primary_cta_label',
            'fr_secondary_cta_label',
            'fr_capabilities',
            'fr_architecture_items',
        ] as $key) {
            unset($data[$key]);
        }

        return [$data, $translations];
    }

    public static function syncTranslations(HeroContent $heroContent, array $translations): void
    {
        foreach ($translations as $locale => $translation) {
            if ($locale !== 'en' && ! self::hasTranslationContent($translation)) {
                $heroContent->translations()->where('locale', $locale)->delete();

                continue;
            }

            $heroContent->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'badge' => $translation['badge'],
                    'headline' => $translation['headline'],
                    'description' => $translation['description'],
                    'primary_cta_label' => $translation['primary_cta_label'],
                    'secondary_cta_label' => $translation['secondary_cta_label'],
                    'capabilities' => $translation['capabilities'],
                    'architecture_items' => $translation['architecture_items'],
                ],
            );
        }
    }

    public static function addTranslationsToFormData(HeroContent $heroContent, array $data): array
    {
        $heroContent->loadMissing('translations');

        foreach (['en', 'fr'] as $locale) {
            $translation = $heroContent->translation($locale);
            $prefix = $locale.'_';

            $data[$prefix.'badge'] = $translation?->badge;
            $data[$prefix.'headline'] = $translation?->headline;
            $data[$prefix.'description'] = $translation?->description;
            $data[$prefix.'primary_cta_label'] = $translation?->primary_cta_label;
            $data[$prefix.'secondary_cta_label'] = $translation?->secondary_cta_label;
            $data[$prefix.'capabilities'] = $translation?->capabilities;
            $data[$prefix.'architecture_items'] = $translation?->architecture_items;
        }

        return $data;
    }

    private static function cleanList(mixed $items): ?array
    {
        $items = collect($items ?? [])
            ->filter(fn (mixed $item): bool => filled($item))
            ->values()
            ->all();

        return $items === [] ? null : $items;
    }

    private static function cleanArchitectureItems(mixed $items): ?array
    {
        $items = collect($items ?? [])
            ->map(fn (array $item): array => [
                'title' => $item['title'] ?? null,
                'description' => $item['description'] ?? null,
            ])
            ->filter(fn (array $item): bool => filled($item['title']) || filled($item['description']))
            ->values()
            ->all();

        return $items === [] ? null : $items;
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
            'index' => Pages\ListHeroContents::route('/'),
            'create' => Pages\CreateHeroContent::route('/create'),
            'edit' => Pages\EditHeroContent::route('/{record}/edit'),
        ];
    }
}
