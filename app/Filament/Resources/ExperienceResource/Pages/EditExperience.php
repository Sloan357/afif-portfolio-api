<?php

namespace App\Filament\Resources\ExperienceResource\Pages;

use App\Filament\Resources\ExperienceResource;
use App\Models\Experience;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditExperience extends EditRecord
{
    protected static string $resource = ExperienceResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return ExperienceResource::addTranslationsToFormData($this->getRecord(), $data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        [$experienceData, $translations] = ExperienceResource::splitExperienceAndTranslationData($data);

        $experienceData['updated_by'] = auth()->id();

        $record->update($experienceData);

        /** @var Experience $record */
        ExperienceResource::syncTranslations($record, $translations);

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
