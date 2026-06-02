<?php

namespace App\Filament\Resources\TechnologyResource\Pages;

use App\Filament\Resources\TechnologyResource;
use App\Models\Technology;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTechnology extends CreateRecord
{
    protected static string $resource = TechnologyResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        return Technology::create($data);
    }
}
