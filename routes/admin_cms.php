<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CMS\MenuItemController;
use App\Http\Controllers\Admin\CMS\ServiceController;

/*
|--------------------------------------------------------------------------
| Admin CMS Routes
|--------------------------------------------------------------------------
|
| These routes handle all CMS-related content management.
|
*/



Route::prefix('admin/cms')->name('admin.cms.')->group(function () {

    // MENU ITEMS
    Route::prefix('menu')->name('menu.')->group(function () {
        Route::get('/', [MenuItemController::class, 'index'])->name('index');
        Route::get('/create', [MenuItemController::class, 'create'])->name('create');
        Route::post('/store', [MenuItemController::class, 'store'])->name('store');
        Route::get('/{menuItem}/edit', [MenuItemController::class, 'edit'])->name('edit');
        Route::put('/{menuItem}', [MenuItemController::class, 'update'])->name('update');
        Route::delete('/{menuItem}', [MenuItemController::class, 'destroy'])->name('destroy');
    });

    
    // CMS: Services

    Route::prefix('services')->name('services.')->group(function () {
    Route::get('/', [ServiceController::class, 'index'])->name('index');
    Route::get('/create', [ServiceController::class, 'create'])->name('create');
    Route::post('/store', [ServiceController::class, 'store'])->name('store');
    Route::get('/{service}/edit', [ServiceController::class, 'edit'])->name('edit');
    Route::put('/{service}', [ServiceController::class, 'update'])->name('update');
    Route::delete('/{service}', [ServiceController::class, 'destroy'])->name('destroy');

});


    // OTHER CMS routes will go here later
});
