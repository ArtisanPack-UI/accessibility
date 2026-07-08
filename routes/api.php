<?php

use ArtisanPack\Accessibility\Ai\Http\Controllers\AriaSuggestionController;
use ArtisanPack\Accessibility\Ai\Http\Controllers\ColorContrastExplanationController;
use ArtisanPack\Accessibility\Ai\Http\Controllers\ContentAccessibilityController;
use ArtisanPack\Accessibility\Http\Controllers\A11yApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('/a11y/contrast-check', [A11yApiController::class, 'contrastCheck']);
    Route::post('/a11y/generate-text-color', [A11yApiController::class, 'generateTextColor']);
    Route::post('/a11y/audit-palette', [A11yApiController::class, 'auditPalette']);

    Route::post('/a11y/ai/content-analysis', ContentAccessibilityController::class)
        ->name('artisanpack-accessibility.ai.content-analysis');
    Route::post('/a11y/ai/aria-suggestion', AriaSuggestionController::class)
        ->name('artisanpack-accessibility.ai.aria-suggestion');
    Route::post('/a11y/ai/contrast-explanation', ColorContrastExplanationController::class)
        ->name('artisanpack-accessibility.ai.contrast-explanation');
});
