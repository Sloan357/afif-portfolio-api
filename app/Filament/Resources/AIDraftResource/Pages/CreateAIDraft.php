<?php

namespace App\Filament\Resources\AIDraftResource\Pages;

use App\Filament\Resources\AIDraftResource;
use App\Models\AIDraft;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAIDraft extends CreateRecord
{
    protected static string $resource = AIDraftResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['created_by'] ??= auth()->id();
        $data['updated_by'] = auth()->id();

        return AIDraft::create($data);
    }
}
