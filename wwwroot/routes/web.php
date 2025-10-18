<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/style-preview', function () {
    return view('style-preview');
});

Route::get('/mobile-preview', function () {
    return view('mobile-preview');

});

    Route::get('/components', function () {
    return view('components');

});

    Route::get('/test-logo', function () {
    return view('test-logo');

});