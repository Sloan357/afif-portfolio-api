<?php

namespace App\Filament\Resources\SiteSettingResource\Pages;

use App\Filament\Resources\SiteSettingResource;
use App\Models\SiteSetting;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditSiteSetting extends EditRecord
{
    protected static string $resource = SiteSettingResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return SiteSettingResource::addTranslationsToFormData($this->getRecord(), $data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        [$siteSettingData, $translations] = SiteSettingResource::splitSiteSettingAndTranslationData($data);

        $siteSettingData['updated_by'] = auth()->id();

        $record->update($siteSettingData);

        /** @var SiteSetting $record */
        SiteSettingResource::syncTranslations($record, $translations);

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
