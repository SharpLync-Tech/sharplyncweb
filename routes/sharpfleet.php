<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SharpFleet Routes
|--------------------------------------------------------------------------
| All SharpFleet application routes live here.
| Controllers and middleware will be added later.
|--------------------------------------------------------------------------
*/

Route::prefix('app/sharpfleet')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authentication (local / Entra later)
    |--------------------------------------------------------------------------
    */
    Route::post('/login', 'SharpFleet\AuthController@login');
    Route::post('/logout', 'SharpFleet\AuthController@logout');

    /*
    |--------------------------------------------------------------------------
    | Driver – Trips
    |--------------------------------------------------------------------------
    */
    Route::post('/trips/start', 'SharpFleet\TripController@start');
    Route::post('/trips/end', 'SharpFleet\TripController@end');
    Route::post('/trips/{trip}/edit', 'SharpFleet\TripController@edit');

    /*
    |--------------------------------------------------------------------------
    | Driver – Faults
    |--------------------------------------------------------------------------
    */
    Route::post('/faults/from-trip', 'SharpFleet\FaultController@storeFromTrip');
    Route::post('/faults/standalone', 'SharpFleet\FaultController@storeStandalone');

    /*
    |--------------------------------------------------------------------------
    | Driver – Bookings
    |--------------------------------------------------------------------------
    */
    Route::get('/bookings/upcoming', 'SharpFleet\BookingController@upcoming');
    Route::post('/bookings/start-trip', 'SharpFleet\BookingController@startTrip');

    /*
    |--------------------------------------------------------------------------
    | Admin – Vehicles
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/vehicles', 'SharpFleet\Admin\VehicleController@index');
    Route::post('/admin/vehicles', 'SharpFleet\Admin\VehicleController@store');
    Route::post('/admin/vehicles/{vehicle}/archive', 'SharpFleet\Admin\VehicleController@archive');

    /*
    |--------------------------------------------------------------------------
    | Admin – Customers
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/customers', 'SharpFleet\Admin\CustomerController@index');
    Route::post('/admin/customers', 'SharpFleet\Admin\CustomerController@store');

    /*
    |--------------------------------------------------------------------------
    | Admin – Bookings
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/bookings', 'SharpFleet\Admin\BookingController@index');
    Route::post('/admin/bookings', 'SharpFleet\Admin\BookingController@store');
    Route::post('/admin/bookings/{booking}/cancel', 'SharpFleet\Admin\BookingController@cancel');

    /*
    |--------------------------------------------------------------------------
    | Admin – Faults
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/faults', 'SharpFleet\Admin\FaultController@index');
    Route::post('/admin/faults/{fault}/status', 'SharpFleet\Admin\FaultController@updateStatus');

    /*
    |--------------------------------------------------------------------------
    | Admin – Reports
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/reports/trips', 'SharpFleet\Admin\ReportController@trips');
    Route::get('/admin/reports/vehicles', 'SharpFleet\Admin\ReportController@vehicles');

});
