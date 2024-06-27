<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class CheckAndCreateCache
{
    /**
     * Cache data if cache is empty
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (!Cache::has('places')) {
            Artisan::call('batch:cache');
        }
        return $next($request);
    }
}
