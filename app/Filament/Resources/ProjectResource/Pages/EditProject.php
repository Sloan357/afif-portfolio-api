<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\Project;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return ProjectResource::addTranslationsToFormData($this->getRecord(), $data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        [$projectData, $translations] = ProjectResource::splitProjectAndTranslationData($data);

        $projectData['updated_by'] = auth()->id();

        $record->update($projectData);

        /** @var Project $record */
        ProjectResource::syncTranslations($record, $translations);

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
