<?php

namespace App\Filament\Resources\HeroContentResource\Pages;

use App\Filament\Resources\HeroContentResource;
use App\Models\HeroContent;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateHeroContent extends CreateRecord
{
    protected static string $resource = HeroContentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        [$heroContentData, $translations] = HeroContentResource::splitHeroContentAndTranslationData($data);

        $heroContentData['created_by'] = auth()->id();
        $heroContentData['updated_by'] = auth()->id();

        $heroContent = HeroContent::create($heroContentData);

        HeroContentResource::syncTranslations($heroContent, $translations);

        return $heroContent;
    }
}
