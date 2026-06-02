<?php

namespace App\Filament\Resources;

use App\Enums\AIDraftTaskType;
use App\Enums\AIRequestStatus;
use App\Filament\Resources\AIRequestLogResource\Pages;
use App\Models\AIRequestLog;
use Filament\Actions\ViewAction;
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

class AIRequestLogResource extends Resource
{
    protected static ?string $model = AIRequestLog::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $recordTitleAttribute = 'uuid';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('AI request log')
                    ->tabs([
                        Tab::make('Request')
                            ->columns(2)
                            ->schema([
                                TextInput::make('uuid')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('ai_draft_id')
                                    ->label('AI draft ID')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('requestable_type')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('requestable_id')
                                    ->disabled()
                                    ->dehydrated(false),

                                Select::make('task_type')
                                    ->options(AIDraftTaskType::options())
                                    ->disabled()
                                    ->dehydrated(false),

                                Select::make('status')
                                    ->options(AIRequestStatus::options())
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('locale')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('source_locale')
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),

                        Tab::make('Provider')
                            ->columns(2)
                            ->schema([
                                TextInput::make('provider')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('model')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('prompt_version')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('request_hash')
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),

                        Tab::make('Cost')
                            ->columns(2)
                            ->schema([
                                TextInput::make('input_tokens')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('output_tokens')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('total_tokens')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('cost_minor_units')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('currency')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('duration_ms')
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),

                        Tab::make('Summaries')
                            ->schema([
                                Textarea::make('input_summary')
                                    ->rows(4)
                                    ->disabled()
                                    ->dehydrated(false),

                                Textarea::make('output_summary')
                                    ->rows(4)
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),

                        Tab::make('Error')
                            ->schema([
                                TextInput::make('error_code')
                                    ->disabled()
                                    ->dehydrated(false),

                                Textarea::make('error_message')
                                    ->rows(4)
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),

                        Tab::make('Metadata')
                            ->schema([
                                KeyValue::make('metadata')
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),

                        Tab::make('Meta')
                            ->columns(2)
                            ->schema([
                                DateTimePicker::make('started_at')
                                    ->disabled()
                                    ->dehydrated(false),

                                DateTimePicker::make('finished_at')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('created_by')
                                    ->label('Created by user ID')
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
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (AIRequestStatus $state): string => str($state->value)->headline()->toString()),

                TextColumn::make('task_type')
                    ->badge()
                    ->formatStateUsing(fn (AIDraftTaskType $state): string => str($state->value)->headline()->toString()),

                TextColumn::make('provider')
                    ->searchable(),

                TextColumn::make('model')
                    ->searchable()
                    ->limit(28),

                TextColumn::make('locale')
                    ->searchable(),

                TextColumn::make('draftable')
                    ->label('Requestable')
                    ->state(fn (AIRequestLog $record): ?string => $record->requestable_type)
                    ->searchable(query: fn ($query, string $search) => $query->where('requestable_type', 'ilike', "%{$search}%"))
                    ->limit(32),

                TextColumn::make('total_tokens')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('cost_minor_units')
                    ->label('Cost')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('duration_ms')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(AIRequestStatus::options()),

                SelectFilter::make('task_type')
                    ->options(AIDraftTaskType::options()),

                SelectFilter::make('provider')
                    ->options(fn (): array => AIRequestLog::query()
                        ->whereNotNull('provider')
                        ->distinct()
                        ->pluck('provider', 'provider')
                        ->all()),

                SelectFilter::make('model')
                    ->options(fn (): array => AIRequestLog::query()
                        ->whereNotNull('model')
                        ->distinct()
                        ->pluck('model', 'model')
                        ->all()),

                SelectFilter::make('locale')
                    ->options([
                        'en' => 'English',
                        'fr' => 'French',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAIRequestLogs::route('/'),
            'view' => Pages\ViewAIRequestLog::route('/{record}'),
        ];
    }
}
