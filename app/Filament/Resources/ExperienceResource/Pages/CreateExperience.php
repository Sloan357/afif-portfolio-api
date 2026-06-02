<?php

namespace App\Filament\Resources\ExperienceResource\Pages;

use App\Filament\Resources\ExperienceResource;
use App\Models\Experience;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateExperience extends CreateRecord
{
    protected static string $resource = ExperienceResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        [$experienceData, $translations] = ExperienceResource::splitExperienceAndTranslationData($data);

        $experienceData['created_by'] = auth()->id();
        $experienceData['updated_by'] = auth()->id();

        $experience = Experience::create($experienceData);

        ExperienceResource::syncTranslations($experience, $translations);

        return $experience;
    }
}
