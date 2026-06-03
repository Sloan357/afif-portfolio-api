<?php

use App\Http\Controllers\Api\V1\BlogPostsController;
use App\Http\Controllers\Api\V1\ExperiencesController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\HeroContentController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\LabProjectsController;
use App\Http\Controllers\Api\V1\ProjectsController;
use App\Http\Controllers\Api\V1\SiteSettingsController;
use App\Http\Controllers\Api\V1\TechnologiesController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('blog-posts', [BlogPostsController::class, 'index'])->name('api.v1.blog-posts.index');
    Route::get('blog-posts/{slug}', [BlogPostsController::class, 'show'])->where('slug', '[A-Za-z0-9-]+')->name('api.v1.blog-posts.show');
    Route::get('experience', [ExperiencesController::class, 'index'])->name('api.v1.experience.index');
    Route::get('health', HealthController::class)->name('api.v1.health');
    Route::get('home', HomeController::class)->name('api.v1.home');
    Route::get('hero', HeroContentController::class)->name('api.v1.hero');
    Route::get('labs', [LabProjectsController::class, 'index'])->name('api.v1.labs.index');
    Route::get('projects', [ProjectsController::class, 'index'])->name('api.v1.projects.index');
    Route::get('projects/{slug}', [ProjectsController::class, 'show'])->where('slug', '[A-Za-z0-9-]+')->name('api.v1.projects.show');
    Route::get('settings', SiteSettingsController::class)->name('api.v1.settings');
    Route::get('technologies', [TechnologiesController::class, 'index'])->name('api.v1.technologies.index');
});
