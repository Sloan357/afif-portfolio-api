<?php

namespace App\Filament\Resources;

use App\Enums\MediaType;
use App\Enums\MediaUsage;
use App\Filament\Resources\MediaResource\Pages;
use App\Models\Media;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static ?string $recordTitleAttribute = 'path';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('path')
                    ->label('Image')
                    ->image()
                    ->disk(fn (): string => config('filesystems.default', 'local'))
                    ->directory('media')
                    ->visibility('public')
                    ->required(),

                TextInput::make('disk')
                    ->default(fn (): string => config('filesystems.default', 'local'))
                    ->required()
                    ->maxLength(255),

                Select::make('type')
                    ->options(MediaType::options())
                    ->default(MediaType::Image->value)
                    ->required(),

                Select::make('usage')
                    ->options(MediaUsage::options())
                    ->default(MediaUsage::General->value)
                    ->required(),

                TextInput::make('alt_text.en')
                    ->label('Alt text (English)')
                    ->maxLength(255),

                TextInput::make('alt_text.fr')
                    ->label('Alt text (French)')
                    ->maxLength(255),

                Textarea::make('caption.en')
                    ->label('Caption (English)')
                    ->rows(3),

                Textarea::make('caption.fr')
                    ->label('Caption (French)')
                    ->rows(3),

                Toggle::make('is_public')
                    ->label('Public')
                    ->default(true),

                TextInput::make('sort_order')
                    ->numeric()
                    ->minValue(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('path')
                    ->label('Image')
                    ->disk(fn (Media $record): string => $record->disk)
                    ->square(),

                TextColumn::make('path')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (MediaType $state): string => str($state->value)->headline()->toString()),

                TextColumn::make('usage')
                    ->badge()
                    ->formatStateUsing(fn (MediaUsage $state): string => str($state->value)->headline()->toString()),

                TextColumn::make('width')
                    ->label('W')
                    ->toggleable(),

                TextColumn::make('height')
                    ->label('H')
                    ->toggleable(),

                TextColumn::make('size_bytes')
                    ->label('Size')
                    ->numeric()
                    ->toggleable(),

                IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(MediaType::options()),

                SelectFilter::make('usage')
                    ->options(MediaUsage::options()),

                TernaryFilter::make('is_public')
                    ->label('Public'),
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
            'index' => Pages\ListMedia::route('/'),
            'create' => Pages\CreateMedia::route('/create'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }
}
