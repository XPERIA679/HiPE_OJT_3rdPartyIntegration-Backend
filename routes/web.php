<?php

use App\Events\CacheUpdated;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-pusher', function () {
    \Log::info('Dispatching CacheUpdated event');
    event(new CacheUpdated('Test message'));
    \Log::info('CacheUpdated event dispatched');
    return "Event dispatched!";
});

Route::get('/pusher-test', function () {
    return view('pusher-test');
});