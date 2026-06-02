<?php

namespace App\Filament\Resources\HeroContentResource\Pages;

use App\Filament\Resources\HeroContentResource;
use App\Models\HeroContent;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditHeroContent extends EditRecord
{
    protected static string $resource = HeroContentResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return HeroContentResource::addTranslationsToFormData($this->getRecord(), $data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        [$heroContentData, $translations] = HeroContentResource::splitHeroContentAndTranslationData($data);

        $heroContentData['updated_by'] = auth()->id();

        $record->update($heroContentData);

        /** @var HeroContent $record */
        HeroContentResource::syncTranslations($record, $translations);

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
