<?php

namespace App\Filament\Resources\AIDraftResource\Pages;

use App\Filament\Resources\AIDraftResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAIDrafts extends ListRecords
{
    protected static string $resource = AIDraftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
