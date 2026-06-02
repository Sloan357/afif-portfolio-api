<?php

namespace App\Filament\Resources\BlogPostResource\Pages;

use App\Filament\Resources\BlogPostResource;
use App\Models\BlogPost;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBlogPost extends CreateRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        [$blogPostData, $translations] = BlogPostResource::splitBlogPostAndTranslationData($data);

        $blogPostData['created_by'] = auth()->id();
        $blogPostData['updated_by'] = auth()->id();

        $blogPost = BlogPost::create($blogPostData);

        BlogPostResource::syncTranslations($blogPost, $translations);

        return $blogPost;
    }
}
