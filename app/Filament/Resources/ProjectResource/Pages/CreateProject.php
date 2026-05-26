<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\Project;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        [$projectData, $translations] = ProjectResource::splitProjectAndTranslationData($data);

        $projectData['created_by'] = auth()->id();
        $projectData['updated_by'] = auth()->id();

        $project = Project::create($projectData);

        ProjectResource::syncTranslations($project, $translations);

        return $project;
    }
}
