<?php

use Illuminate\Support\Facades\Route;

Route::prefix('products')->name('products.')->group(function () {
    Route::view('/sharpfleet', 'pages.products.sharpfleet')->name('sharpfleet');
});
