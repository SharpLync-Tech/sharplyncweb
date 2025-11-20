<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CMS\MenuItemController;
use App\Http\Controllers\Admin\CMS\ServiceController;
use App\Http\Controllers\Admin\CMS\PageController;
use App\Http\Controllers\Admin\CMS\FooterLinkController;
use App\Http\Controllers\Admin\CMS\AboutSectionController;
use App\Http\Controllers\Admin\CMS\AboutTimelineItemController;
use App\Http\Controllers\Admin\CMS\AboutValueController;
use App\Http\Controllers\Admin\CMS\ContactInfoController;
use App\Http\Controllers\Admin\CMS\SeoMetaController;
use App\Http\Controllers\Admin\CMS\BlogCategoryController;
use App\Http\Controllers\Admin\CMS\BlogPostController;

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

   
        // CMS: About Timeline
        Route::prefix('about/timeline')->name('about.timeline.')->group(function () {
        Route::get('/', [AboutTimelineItemController::class, 'index'])->name('index');
        Route::get('/create', [AboutTimelineItemController::class, 'create'])->name('create');
        Route::post('/store', [AboutTimelineItemController::class, 'store'])->name('store');
        Route::get('/{timelineItem}/edit', [AboutTimelineItemController::class, 'edit'])->name('edit');
        Route::put('/{timelineItem}', [AboutTimelineItemController::class, 'update'])->name('update');
        Route::delete('/{timelineItem}', [AboutTimelineItemController::class, 'destroy'])->name('destroy');
    });

    
        // CMS: About Values
        Route::prefix('about/values')->name('about.values.')->group(function () {
        Route::get('/', [AboutValueController::class, 'index'])->name('index');
        Route::get('/create', [AboutValueController::class, 'create'])->name('create');
        Route::post('/store', [AboutValueController::class, 'store'])->name('store');
        Route::get('/{value}/edit', [AboutValueController::class, 'edit'])->name('edit');
        Route::put('/{value}', [AboutValueController::class, 'update'])->name('update');
        Route::delete('/{value}', [AboutValueController::class, 'destroy'])->name('destroy');
    });

    
        // CMS: Contact Info
        Route::prefix('contact')->name('contact.')->group(function () {
        Route::get('/', [ContactInfoController::class, 'index'])->name('index');
        Route::get('/{contact}/edit', [ContactInfoController::class, 'edit'])->name('edit');
        Route::put('/{contact}', [ContactInfoController::class, 'update'])->name('update');
    });

    
        // CMS: SEO Meta
        Route::prefix('seo')->name('seo.')->group(function () {
        Route::get('/', [SeoMetaController::class, 'index'])->name('index');
        Route::get('/create', [SeoMetaController::class, 'create'])->name('create');
        Route::post('/store', [SeoMetaController::class, 'store'])->name('store');
        Route::get('/{seoMeta}/edit', [SeoMetaController::class, 'edit'])->name('edit');
        Route::put('/{seoMeta}', [SeoMetaController::class, 'update'])->name('update');
        Route::delete('/{seoMeta}', [SeoMetaController::class, 'destroy'])->name('destroy');
    });


        // CMS: Blog Categories
        Route::prefix('blog/categories')->name('blog.categories.')->group(function () {
        Route::get('/', [BlogCategoryController::class, 'index'])->name('index');
        Route::get('/create', [BlogCategoryController::class, 'create'])->name('create');
        Route::post('/store', [BlogCategoryController::class, 'store'])->name('store');
        Route::get('/{category}/edit', [BlogCategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [BlogCategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [BlogCategoryController::class, 'destroy'])->name('destroy');
    });

    
        // CMS: Blog Posts



        Route::prefix('blog/posts')->name('blog.posts.')->group(function () {
        Route::get('/', [BlogPostController::class, 'index'])->name('index');
        Route::get('/create', [BlogPostController::class, 'create'])->name('create');
        Route::post('/store', [BlogPostController::class, 'store'])->name('store');
        Route::get('/{post}/edit', [BlogPostController::class, 'edit'])->name('edit');
        Route::put('/{post}', [BlogPostController::class, 'update'])->name('update');
        Route::delete('/{post}', [BlogPostController::class, 'destroy'])->name('destroy');
    });


    
    
    // OTHER CMS routes will go here later
});
