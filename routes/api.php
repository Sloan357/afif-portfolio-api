<?php

use App\Http\Controllers\Api\V1\ExperiencesController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\HeroContentController;
use App\Http\Controllers\Api\V1\LabProjectsController;
use App\Http\Controllers\Api\V1\ProjectsController;
use App\Http\Controllers\Api\V1\SiteSettingsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('experience', [ExperiencesController::class, 'index'])->name('api.v1.experience.index');
    Route::get('health', HealthController::class)->name('api.v1.health');
    Route::get('hero', HeroContentController::class)->name('api.v1.hero');
    Route::get('labs', [LabProjectsController::class, 'index'])->name('api.v1.labs.index');
    Route::get('projects', [ProjectsController::class, 'index'])->name('api.v1.projects.index');
    Route::get('projects/{slug}', [ProjectsController::class, 'show'])->name('api.v1.projects.show');
    Route::get('settings', SiteSettingsController::class)->name('api.v1.settings');
});
