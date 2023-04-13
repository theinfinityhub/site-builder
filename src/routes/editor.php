<?php

use Illuminate\Support\Facades\Route;
use OneClx\SiteBuilder\Http\Controllers\SiteEditorController;


Route::middleware(['web', 'csrf', 'verified', 'role:pcx'])->controller(SiteEditorController::class)->group(function () {
    Route::get('{business}', 'editor')->name('editor');
    Route::get('business', 'business')->name('index');
    Route::post('upload', 'upload')->name('upload');
    Route::post('save', 'save')->name('save');
    Route::get('public/scan', 'scan')->name('scan');
    Route::post('reset', 'reset')->name('reset');
});
