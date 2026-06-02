<?php

namespace App\Filament\Resources\AIDraftResource\Pages;

use App\Filament\Resources\AIDraftResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditAIDraft extends EditRecord
{
    protected static string $resource = AIDraftResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data['updated_by'] = auth()->id();

        $record->update($data);

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
