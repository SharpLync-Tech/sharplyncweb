<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SharpLync Tools Routes
|--------------------------------------------------------------------------
| Grouped routes for public-facing tools such as:
| - Cyber Glossary
| - Cyber Check (future)
|--------------------------------------------------------------------------
*/

Route::prefix('tools')->group(function () {

    // Cyber Glossary
    Route::get('/cyber-glossary', function () {
        return view('tools.cyber-glossary.pages.index');
    })->name('tools.cyber-glossary');

    /*
    |--------------------------------------------------------------------------
    | Cyber Check (scaffold only for now)
    |--------------------------------------------------------------------------
    */
    // Route::get('/cyber-check', function () {
    //     return view('tools.cyber-check.pages.start');
    // })->name('tools.cyber-check');

});
