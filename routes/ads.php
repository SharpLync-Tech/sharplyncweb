<?php

use Illuminate\Support\Facades\Route;

// =====================================
// Google Ads Landing Pages (Isolated)
// =====================================
Route::prefix('ads')->group(function () {

    // Core service pages
    Route::view('/it-support', 'ads.pages.it-support')->name('ads.it-support');
    Route::view('/managed-it', 'ads.pages.managed-it')->name('ads.managed-it');
    Route::view('/cybersecurity', 'ads.pages.cybersecurity')->name('ads.cybersecurity');
    Route::view('/microsoft-365', 'ads.pages.microsoft-365')->name('ads.microsoft365');
    Route::view('/wifi-network', 'ads.pages.wifi-network')->name('ads.wifi');
    Route::view('/emergency-it', 'ads.pages.emergency-it')->name('ads.emergency');
    Route::view('/backup-recovery', 'ads.pages.backup-recovery')->name('ads.backup');
    Route::view('/safecheck', 'ads.pages.safecheck')->name('ads.safecheck');
    Route::view('/remote-support', 'ads.pages.remote-support-ads')->name('ads.remote');
    Route::view('/consultation', 'ads.pages.consultation')->name('ads.consultation');
    Route::view('/hardware-setup', 'ads.pages.hardware-setup')->name('ads.hardware');

    // Regional SEO pages
    Route::prefix('regions')->group(function () {
        Route::view('/stanthorpe', 'ads.pages.regions.stanthorpe')->name('ads.stanthorpe');
        Route::view('/warwick', 'ads.pages.regions.warwick')->name('ads.warwick');
        Route::view('/toowoomba', 'ads.pages.regions.toowoomba')->name('ads.toowoomba');
        Route::view('/granite-belt', 'ads.pages.regions.granite-belt')->name('ads.granitebelt');
    });

});
