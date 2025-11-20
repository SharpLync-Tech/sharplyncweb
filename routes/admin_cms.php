<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin CMS Routes
|--------------------------------------------------------------------------
|
| These routes handle all CMS-related content management.
|
*/

use App\Http\Controllers\Admin\CMS\MenuItemController;

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

    // OTHER CMS routes will go here later
});
