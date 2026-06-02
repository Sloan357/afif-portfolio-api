<?php

namespace App\Filament\Resources\SiteSettingResource\Pages;

use App\Filament\Resources\SiteSettingResource;
use App\Models\SiteSetting;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSiteSetting extends CreateRecord
{
    protected static string $resource = SiteSettingResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        [$siteSettingData, $translations] = SiteSettingResource::splitSiteSettingAndTranslationData($data);

        $siteSettingData['created_by'] = auth()->id();
        $siteSettingData['updated_by'] = auth()->id();

        $siteSetting = SiteSetting::create($siteSettingData);

        SiteSettingResource::syncTranslations($siteSetting, $translations);

        return $siteSetting;
    }
}
