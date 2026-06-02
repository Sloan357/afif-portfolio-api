<?php

namespace App\Filament\Resources\HeroContentResource\Pages;

use App\Filament\Resources\HeroContentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListHeroContents extends ListRecords
{
    protected static string $resource = HeroContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getTableQuery(): ?Builder
    {
        return parent::getTableQuery()?->with(['heroImage', 'translations']);
    }
}
