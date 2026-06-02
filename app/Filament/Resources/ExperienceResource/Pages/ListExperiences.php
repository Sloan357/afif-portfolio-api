<?php

namespace App\Filament\Resources\ExperienceResource\Pages;

use App\Filament\Resources\ExperienceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListExperiences extends ListRecords
{
    protected static string $resource = ExperienceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getTableQuery(): ?Builder
    {
        return parent::getTableQuery()?->with('translations');
    }
}
