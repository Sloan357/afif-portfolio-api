<?php

namespace App\Filament\Resources;

use App\Enums\AIDraftStatus;
use App\Enums\AIDraftTaskType;
use App\Filament\Resources\AIDraftResource\Pages;
use App\Models\AIDraft;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AIDraftResource extends Resource
{
    protected static ?string $model = AIDraft::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('AI draft')
                    ->tabs([
                        Tab::make('Draft')
                            ->columns(2)
                            ->schema([
                                TextInput::make('title')
                                    ->maxLength(255),

                                Select::make('task_type')
                                    ->options(AIDraftTaskType::options())
                                    ->required(),

                                Select::make('status')
                                    ->options(AIDraftStatus::options())
                                    ->default(AIDraftStatus::Draft->value)
                                    ->required(),

                                TextInput::make('field')
                                    ->maxLength(255),

                                TextInput::make('locale')
                                    ->maxLength(20),

                                TextInput::make('source_locale')
                                    ->default('en')
                                    ->maxLength(20),

                                TextInput::make('draftable_type')
                                    ->maxLength(255),

                                TextInput::make('draftable_id')
                                    ->numeric()
                                    ->minValue(0),

                                KeyValue::make('draft_value')
                                    ->required()
                                    ->columnSpanFull(),

                                KeyValue::make('input_snapshot')
                                    ->columnSpanFull(),

                                Textarea::make('notes')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('AI Metadata')
                            ->columns(2)
                            ->schema([
                                TextInput::make('provider')
                                    ->maxLength(255),

                                TextInput::make('model')
                                    ->maxLength(255),

                                TextInput::make('prompt_version')
                                    ->maxLength(255),

                                TextInput::make('source_hash')
                                    ->maxLength(255),
                            ]),

                        Tab::make('Review')
                            ->columns(2)
                            ->schema([
                                TextInput::make('reviewed_by')
                                    ->label('Reviewed by user ID')
                                    ->numeric()
                                    ->minValue(0),

                                DateTimePicker::make('reviewed_at'),

                                TextInput::make('applied_by')
                                    ->label('Applied by user ID')
                                    ->numeric()
                                    ->minValue(0),

                                DateTimePicker::make('applied_at'),

                                TextInput::make('rejected_by')
                                    ->label('Rejected by user ID')
                                    ->numeric()
                                    ->minValue(0),

                                DateTimePicker::make('rejected_at'),
                            ]),

                        Tab::make('Meta')
                            ->columns(2)
                            ->schema([
                                TextInput::make('uuid')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('created_by')
                                    ->label('Created by user ID')
                                    ->numeric()
                                    ->minValue(0),

                                TextInput::make('updated_by')
                                    ->label('Updated by user ID')
                                    ->numeric()
                                    ->minValue(0),

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
                TextColumn::make('title')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('task_type')
                    ->badge()
                    ->formatStateUsing(fn (AIDraftTaskType $state): string => str($state->value)->headline()->toString()),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (AIDraftStatus $state): string => str($state->value)->headline()->toString()),

                TextColumn::make('locale')
                    ->searchable(),

                TextColumn::make('draftable_type')
                    ->label('Draftable')
                    ->searchable()
                    ->limit(32),

                TextColumn::make('field')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('provider')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('model')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(AIDraftStatus::options()),

                SelectFilter::make('task_type')
                    ->options(AIDraftTaskType::options()),

                SelectFilter::make('locale')
                    ->options([
                        'en' => 'English',
                        'fr' => 'French',
                    ]),

                SelectFilter::make('draftable_type')
                    ->options(fn (): array => AIDraft::query()
                        ->whereNotNull('draftable_type')
                        ->distinct()
                        ->pluck('draftable_type', 'draftable_type')
                        ->all()),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAIDrafts::route('/'),
            'create' => Pages\CreateAIDraft::route('/create'),
            'edit' => Pages\EditAIDraft::route('/{record}/edit'),
        ];
    }
}
