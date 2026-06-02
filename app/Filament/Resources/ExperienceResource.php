<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExperienceResource\Pages;
use App\Models\Experience;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExperienceResource extends Resource
{
    protected static ?string $model = Experience::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $recordTitleAttribute = 'company';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Experience')
                    ->tabs([
                        Tab::make('Content')
                            ->columns(2)
                            ->schema([
                                TextInput::make('company')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('company_url')
                                    ->label('Company URL')
                                    ->url()
                                    ->maxLength(255),

                                TextInput::make('location')
                                    ->maxLength(255),

                                TextInput::make('en_role')
                                    ->label('Role (English)')
                                    ->required()
                                    ->maxLength(255)
                                    ->dehydrated(),

                                Textarea::make('en_summary')
                                    ->label('Summary (English)')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->dehydrated(),

                                Repeater::make('en_responsibilities')
                                    ->label('Responsibilities (English)')
                                    ->simple(TextInput::make('responsibility')->maxLength(500))
                                    ->columnSpanFull()
                                    ->dehydrated(),

                                TextInput::make('fr_role')
                                    ->label('Role (French)')
                                    ->maxLength(255)
                                    ->dehydrated(),

                                Textarea::make('fr_summary')
                                    ->label('Summary (French)')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->dehydrated(),

                                Repeater::make('fr_responsibilities')
                                    ->label('Responsibilities (French)')
                                    ->simple(TextInput::make('responsibility')->maxLength(500))
                                    ->columnSpanFull()
                                    ->dehydrated(),
                            ]),

                        Tab::make('Period')
                            ->columns(2)
                            ->schema([
                                DatePicker::make('start_date'),

                                DatePicker::make('end_date'),

                                Toggle::make('is_current')
                                    ->label('Current role')
                                    ->default(false),

                                Toggle::make('is_visible')
                                    ->label('Visible')
                                    ->default(true),

                                TextInput::make('sort_order')
                                    ->numeric()
                                    ->minValue(0),
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

                                DatePicker::make('created_at')
                                    ->disabled()
                                    ->dehydrated(false),

                                DatePicker::make('updated_at')
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
                TextColumn::make('role')
                    ->label('Role')
                    ->state(fn (Experience $record): ?string => $record->translation('en')?->role)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('translations', function (Builder $query) use ($search): void {
                            $query
                                ->where('locale', 'en')
                                ->where('role', 'ilike', "%{$search}%");
                        });
                    }),

                TextColumn::make('company')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),

                TextColumn::make('end_date')
                    ->date()
                    ->sortable(),

                IconColumn::make('is_current')
                    ->label('Current')
                    ->boolean(),

                IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_current')
                    ->label('Current'),

                TernaryFilter::make('is_visible')
                    ->label('Visible'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_date', 'desc');
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, array<string, mixed>>}
     */
    public static function splitExperienceAndTranslationData(array $data): array
    {
        $translations = [
            'en' => [
                'role' => $data['en_role'] ?? null,
                'summary' => $data['en_summary'] ?? null,
                'responsibilities' => self::cleanResponsibilities($data['en_responsibilities'] ?? null),
            ],
            'fr' => [
                'role' => $data['fr_role'] ?? null,
                'summary' => $data['fr_summary'] ?? null,
                'responsibilities' => self::cleanResponsibilities($data['fr_responsibilities'] ?? null),
            ],
        ];

        foreach ([
            'en_role',
            'en_summary',
            'en_responsibilities',
            'fr_role',
            'fr_summary',
            'fr_responsibilities',
        ] as $key) {
            unset($data[$key]);
        }

        return [$data, $translations];
    }

    public static function syncTranslations(Experience $experience, array $translations): void
    {
        foreach ($translations as $locale => $translation) {
            if ($locale !== 'en' && ! self::hasTranslationContent($translation)) {
                $experience->translations()->where('locale', $locale)->delete();

                continue;
            }

            $experience->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'role' => $translation['role'],
                    'summary' => $translation['summary'],
                    'responsibilities' => $translation['responsibilities'],
                ],
            );
        }
    }

    public static function addTranslationsToFormData(Experience $experience, array $data): array
    {
        $experience->loadMissing('translations');

        foreach (['en', 'fr'] as $locale) {
            $translation = $experience->translation($locale);
            $prefix = $locale.'_';

            $data[$prefix.'role'] = $translation?->role;
            $data[$prefix.'summary'] = $translation?->summary;
            $data[$prefix.'responsibilities'] = $translation?->responsibilities;
        }

        return $data;
    }

    private static function cleanResponsibilities(mixed $responsibilities): ?array
    {
        $responsibilities = collect($responsibilities ?? [])
            ->filter(fn (mixed $responsibility): bool => filled($responsibility))
            ->values()
            ->all();

        return $responsibilities === [] ? null : $responsibilities;
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
            'index' => Pages\ListExperiences::route('/'),
            'create' => Pages\CreateExperience::route('/create'),
            'edit' => Pages\EditExperience::route('/{record}/edit'),
        ];
    }
}
