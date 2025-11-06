<?php

use ArtisanPack\Accessibility\Http\Controllers\A11yApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('/a11y/contrast-check', [A11yApiController::class, 'contrastCheck']);
    Route::post('/a11y/generate-text-color', [A11yApiController::class, 'generateTextColor']);
    Route::post('/a11y/audit-palette', [A11yApiController::class, 'auditPalette']);
});
