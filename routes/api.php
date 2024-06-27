<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PlaceController;

Route::get('/places', [PlaceController::class, 'generalList']);
Route::get('/places/weather', [PlaceController::class, 'listByCurrentBaseWeather']);
Route::get('/place/{geoapifyId}', [PlaceController::class, 'singleCurrentDetails']);
Route::get('/place/{geoapifyId}/details', [PlaceController::class, 'singleGetFullDetails']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/current-location', [PlaceController::class, 'currentLocation']);
