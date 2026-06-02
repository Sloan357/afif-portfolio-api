<?php

namespace App\Filament\Resources\BlogPostResource\Pages;

use App\Filament\Resources\BlogPostResource;
use App\Models\BlogPost;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditBlogPost extends EditRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return BlogPostResource::addTranslationsToFormData($this->getRecord(), $data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        [$blogPostData, $translations] = BlogPostResource::splitBlogPostAndTranslationData($data);

        $blogPostData['updated_by'] = auth()->id();

        $record->update($blogPostData);

        /** @var BlogPost $record */
        BlogPostResource::syncTranslations($record, $translations);

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
