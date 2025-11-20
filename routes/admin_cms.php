<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CMS\MenuItemController;
use App\Http\Controllers\Admin\CMS\ServiceController;
use App\Http\Controllers\Admin\CMS\PageController;
use App\Http\Controllers\Admin\CMS\FooterLinkController;
use App\Http\Controllers\Admin\CMS\AboutSectionController;

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
    
        // CMS: Pages
        Route::prefix('pages')->name('pages.')->group(function () {
        Route::get('/', [PageController::class, 'index'])->name('index');
        Route::get('/create', [PageController::class, 'create'])->name('create');
        Route::post('/store', [PageController::class, 'store'])->name('store');
        Route::get('/{page}/edit', [PageController::class, 'edit'])->name('edit');
        Route::put('/{page}', [PageController::class, 'update'])->name('update');
        Route::delete('/{page}', [PageController::class, 'destroy'])->name('destroy');
    });

   
        // CMS: Footer Links
        Route::prefix('footer')->name('footer.')->group(function () {
        Route::get('/', [FooterLinkController::class, 'index'])->name('index');
        Route::get('/create', [FooterLinkController::class, 'create'])->name('create');
        Route::post('/store', [FooterLinkController::class, 'store'])->name('store');
        Route::get('/{footerLink}/edit', [FooterLinkController::class, 'edit'])->name('edit');
        Route::put('/{footerLink}', [FooterLinkController::class, 'update'])->name('update');
        Route::delete('/{footerLink}', [FooterLinkController::class, 'destroy'])->name('destroy');
    });

    
        // CMS: About Sections
        Route::prefix('about/sections')->name('about.sections.')->group(function () {
        Route::get('/', [AboutSectionController::class, 'index'])->name('index');
        Route::get('/create', [AboutSectionController::class, 'create'])->name('create');
        Route::post('/store', [AboutSectionController::class, 'store'])->name('store');
        Route::get('/{section}/edit', [AboutSectionController::class, 'edit'])->name('edit');
        Route::put('/{section}', [AboutSectionController::class, 'update'])->name('update');
        Route::delete('/{section}', [AboutSectionController::class, 'destroy'])->name('destroy');
    });

    
    // OTHER CMS routes will go here later
});
