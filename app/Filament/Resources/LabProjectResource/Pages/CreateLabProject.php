<?php

namespace App\Filament\Resources\LabProjectResource\Pages;

use App\Filament\Resources\LabProjectResource;
use App\Models\LabProject;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateLabProject extends CreateRecord
{
    protected static string $resource = LabProjectResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        [$labProjectData, $translations] = LabProjectResource::splitLabProjectAndTranslationData($data);

        $labProjectData['created_by'] = auth()->id();
        $labProjectData['updated_by'] = auth()->id();

        $labProject = LabProject::create($labProjectData);

        LabProjectResource::syncTranslations($labProject, $translations);

        return $labProject;
    }
}
