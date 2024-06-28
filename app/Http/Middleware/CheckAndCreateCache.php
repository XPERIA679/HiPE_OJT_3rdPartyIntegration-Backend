<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class CheckAndCreateCache
{
    /**
     * Cache data if cache is empty
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (!Cache::has('places')) {
            $lockKey = 'creating_places_cache';
            if (!Cache::get($lockKey)) {
                Cache::put($lockKey, true, 60); // Lock for 60 seconds
                try {
                    Artisan::call('batch:cache');
                    Log::info('Places cache created on-demand');
                } catch (\Exception $e) {
                    Log::error('Failed to create places cache: ' . $e->getMessage());
                } finally {
                    Cache::forget($lockKey);
                }
            }
        }
        return $next($request);
    }
}
