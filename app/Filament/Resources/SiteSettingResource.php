<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteSettingResource\Pages;
use App\Models\SiteSetting;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
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
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $recordTitleAttribute = 'site_name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Site settings')
                    ->tabs([
                        Tab::make('General')
                            ->columns(2)
                            ->schema([
                                TextInput::make('key')
                                    ->default('default')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),

                                TextInput::make('site_name')
                                    ->required()
                                    ->maxLength(255),

                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),

                                TextInput::make('primary_domain')
                                    ->maxLength(255),

                                TextInput::make('frontend_url')
                                    ->label('Frontend URL')
                                    ->url()
                                    ->maxLength(255),

                                TextInput::make('admin_url')
                                    ->label('Admin URL')
                                    ->url()
                                    ->maxLength(255),

                                TextInput::make('email')
                                    ->email()
                                    ->maxLength(255),

                                TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(255),

                                TextInput::make('location')
                                    ->maxLength(255),
                            ]),

                        Tab::make('Localized Copy')
                            ->columns(2)
                            ->schema([
                                TextInput::make('en_tagline')
                                    ->label('Tagline (English)')
                                    ->maxLength(255)
                                    ->dehydrated(),

                                TextInput::make('fr_tagline')
                                    ->label('Tagline (French)')
                                    ->maxLength(255)
                                    ->dehydrated(),

                                Textarea::make('en_description')
                                    ->label('Description (English)')
                                    ->rows(3)
                                    ->dehydrated(),

                                Textarea::make('fr_description')
                                    ->label('Description (French)')
                                    ->rows(3)
                                    ->dehydrated(),

                                Textarea::make('en_footer_text')
                                    ->label('Footer text (English)')
                                    ->rows(3)
                                    ->dehydrated(),

                                Textarea::make('fr_footer_text')
                                    ->label('Footer text (French)')
                                    ->rows(3)
                                    ->dehydrated(),
                            ]),

                        Tab::make('Social / Contact')
                            ->schema([
                                Repeater::make('social_links')
                                    ->schema([
                                        TextInput::make('platform')
                                            ->required()
                                            ->maxLength(255),

                                        TextInput::make('label')
                                            ->required()
                                            ->maxLength(255),

                                        TextInput::make('url')
                                            ->url()
                                            ->required()
                                            ->maxLength(255),

                                        TextInput::make('sort_order')
                                            ->numeric()
                                            ->minValue(0),

                                        Toggle::make('is_visible')
                                            ->default(true),
                                    ]),

                                Repeater::make('contact_links')
                                    ->schema([
                                        TextInput::make('type')
                                            ->required()
                                            ->maxLength(255),

                                        TextInput::make('label')
                                            ->required()
                                            ->maxLength(255),

                                        TextInput::make('value')
                                            ->maxLength(255),

                                        TextInput::make('url')
                                            ->maxLength(255),

                                        TextInput::make('sort_order')
                                            ->numeric()
                                            ->minValue(0),

                                        Toggle::make('is_visible')
                                            ->default(true),
                                    ]),
                            ]),

                        Tab::make('SEO')
                            ->columns(2)
                            ->schema([
                                TextInput::make('en_default_seo_title')
                                    ->label('Default SEO title (English)')
                                    ->maxLength(255)
                                    ->dehydrated(),

                                TextInput::make('fr_default_seo_title')
                                    ->label('Default SEO title (French)')
                                    ->maxLength(255)
                                    ->dehydrated(),

                                Textarea::make('en_default_seo_description')
                                    ->label('Default SEO description (English)')
                                    ->rows(3)
                                    ->dehydrated(),

                                Textarea::make('fr_default_seo_description')
                                    ->label('Default SEO description (French)')
                                    ->rows(3)
                                    ->dehydrated(),

                                TagsInput::make('en_default_seo_keywords')
                                    ->label('Default SEO keywords (English)')
                                    ->dehydrated(),

                                TagsInput::make('fr_default_seo_keywords')
                                    ->label('Default SEO keywords (French)')
                                    ->dehydrated(),

                                KeyValue::make('metadata')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Media')
                            ->schema([
                                Select::make('default_og_image_id')
                                    ->label('Default OG image')
                                    ->relationship('defaultOgImage', 'path')
                                    ->searchable()
                                    ->preload(),

                                Select::make('favicon_media_id')
                                    ->label('Favicon media')
                                    ->relationship('faviconMedia', 'path')
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('defaultOgImage.path')
                    ->label('OG')
                    ->disk(fn (SiteSetting $record): ?string => $record->defaultOgImage?->disk)
                    ->square(),

                TextColumn::make('site_name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('key')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('primary_domain')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
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
    public static function splitSiteSettingAndTranslationData(array $data): array
    {
        $translations = [
            'en' => [
                'tagline' => $data['en_tagline'] ?? null,
                'description' => $data['en_description'] ?? null,
                'default_seo_title' => $data['en_default_seo_title'] ?? null,
                'default_seo_description' => $data['en_default_seo_description'] ?? null,
                'default_seo_keywords' => $data['en_default_seo_keywords'] ?? null,
                'footer_text' => $data['en_footer_text'] ?? null,
            ],
            'fr' => [
                'tagline' => $data['fr_tagline'] ?? null,
                'description' => $data['fr_description'] ?? null,
                'default_seo_title' => $data['fr_default_seo_title'] ?? null,
                'default_seo_description' => $data['fr_default_seo_description'] ?? null,
                'default_seo_keywords' => $data['fr_default_seo_keywords'] ?? null,
                'footer_text' => $data['fr_footer_text'] ?? null,
            ],
        ];

        foreach ([
            'en_tagline',
            'en_description',
            'en_default_seo_title',
            'en_default_seo_description',
            'en_default_seo_keywords',
            'en_footer_text',
            'fr_tagline',
            'fr_description',
            'fr_default_seo_title',
            'fr_default_seo_description',
            'fr_default_seo_keywords',
            'fr_footer_text',
        ] as $key) {
            unset($data[$key]);
        }

        $data['social_links'] = self::cleanLinks($data['social_links'] ?? null, ['platform', 'label', 'url']);
        $data['contact_links'] = self::cleanLinks($data['contact_links'] ?? null, ['type', 'label', 'value', 'url']);

        return [$data, $translations];
    }

    public static function syncTranslations(SiteSetting $siteSetting, array $translations): void
    {
        foreach ($translations as $locale => $translation) {
            if ($locale !== 'en' && ! self::hasTranslationContent($translation)) {
                $siteSetting->translations()->where('locale', $locale)->delete();

                continue;
            }

            $siteSetting->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'tagline' => $translation['tagline'],
                    'description' => $translation['description'],
                    'default_seo_title' => $translation['default_seo_title'],
                    'default_seo_description' => $translation['default_seo_description'],
                    'default_seo_keywords' => $translation['default_seo_keywords'],
                    'footer_text' => $translation['footer_text'],
                ],
            );
        }
    }

    public static function addTranslationsToFormData(SiteSetting $siteSetting, array $data): array
    {
        $siteSetting->loadMissing('translations');

        foreach (['en', 'fr'] as $locale) {
            $translation = $siteSetting->translation($locale);
            $prefix = $locale.'_';

            $data[$prefix.'tagline'] = $translation?->tagline;
            $data[$prefix.'description'] = $translation?->description;
            $data[$prefix.'default_seo_title'] = $translation?->default_seo_title;
            $data[$prefix.'default_seo_description'] = $translation?->default_seo_description;
            $data[$prefix.'default_seo_keywords'] = $translation?->default_seo_keywords;
            $data[$prefix.'footer_text'] = $translation?->footer_text;
        }

        return $data;
    }

    private static function cleanLinks(mixed $links, array $contentKeys): ?array
    {
        $links = collect($links ?? [])
            ->map(function (array $link): array {
                $link['is_visible'] = (bool) ($link['is_visible'] ?? true);

                if (array_key_exists('sort_order', $link) && $link['sort_order'] !== null && $link['sort_order'] !== '') {
                    $link['sort_order'] = (int) $link['sort_order'];
                }

                return $link;
            })
            ->filter(function (array $link) use ($contentKeys): bool {
                foreach ($contentKeys as $key) {
                    if (filled($link[$key] ?? null)) {
                        return true;
                    }
                }

                return false;
            })
            ->values()
            ->all();

        return $links === [] ? null : $links;
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
            'index' => Pages\ListSiteSettings::route('/'),
            'create' => Pages\CreateSiteSetting::route('/create'),
            'edit' => Pages\EditSiteSetting::route('/{record}/edit'),
        ];
    }
}
