<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/style-preview', function () {
    return view('style-preview');
});

Route::get('/test-blade', function () {
    return view('testdeploy');
});