<?php

namespace App\Filament\Resources\LabProjectResource\Pages;

use App\Filament\Resources\LabProjectResource;
use App\Models\LabProject;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditLabProject extends EditRecord
{
    protected static string $resource = LabProjectResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return LabProjectResource::addTranslationsToFormData($this->getRecord(), $data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        [$labProjectData, $translations] = LabProjectResource::splitLabProjectAndTranslationData($data);

        $labProjectData['updated_by'] = auth()->id();

        $record->update($labProjectData);

        /** @var LabProject $record */
        LabProjectResource::syncTranslations($record, $translations);

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
