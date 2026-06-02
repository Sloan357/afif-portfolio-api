<?php

namespace App\Filament\Resources;

use App\Enums\TechnologyCategory;
use App\Filament\Resources\TechnologyResource\Pages;
use App\Models\Technology;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
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

class TechnologyResource extends Resource
{
    protected static ?string $model = Technology::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-code-bracket-square';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Technology')
                    ->tabs([
                        Tab::make('Details')
                            ->columns(2)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),

                                Select::make('category')
                                    ->options(TechnologyCategory::options()),

                                TextInput::make('website_url')
                                    ->label('Website URL')
                                    ->url()
                                    ->maxLength(255),

                                Select::make('icon_media_id')
                                    ->label('Icon media')
                                    ->relationship('iconMedia', 'path')
                                    ->searchable()
                                    ->preload(),

                                ColorPicker::make('color'),

                                TextInput::make('sort_order')
                                    ->numeric()
                                    ->minValue(0),

                                Toggle::make('is_visible')
                                    ->label('Visible')
                                    ->default(true),
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
                ImageColumn::make('iconMedia.path')
                    ->label('Icon')
                    ->disk(fn (Technology $record): ?string => $record->iconMedia?->disk)
                    ->square(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn (?TechnologyCategory $state): ?string => $state ? str($state->value)->headline()->toString() : null),

                TextColumn::make('color')
                    ->toggleable(),

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
                SelectFilter::make('category')
                    ->options(TechnologyCategory::options()),

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
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTechnologies::route('/'),
            'create' => Pages\CreateTechnology::route('/create'),
            'edit' => Pages\EditTechnology::route('/{record}/edit'),
        ];
    }
}
