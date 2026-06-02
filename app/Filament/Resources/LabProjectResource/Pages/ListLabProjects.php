<?php

namespace App\Filament\Resources\LabProjectResource\Pages;

use App\Filament\Resources\LabProjectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListLabProjects extends ListRecords
{
    protected static string $resource = LabProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getTableQuery(): ?Builder
    {
        return parent::getTableQuery()?->with(['featuredImage', 'translations']);
    }
}
