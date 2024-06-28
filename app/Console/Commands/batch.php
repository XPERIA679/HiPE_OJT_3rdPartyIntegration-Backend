<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class batch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:cache {--limit=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache places from Geoapify API';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $limit = $this->option('limit');
        $apiKey = env('GEOAPIFY_API_KEY');

        $response = Http::get('https://api.geoapify.com/v2/places', [
            'categories' => 'populated_place.city,populated_place.town',
            'filter' => 'rect:114.1036921,4.3833333,126.803083,21.321928',
            'bias' => 'proximity:121.0744066,14.6177381',
            'limit' => $limit,
            'apiKey' => $apiKey,
        ]);

        try {
            if (!$response->successful()) {
                throw new \Exception('Failed to fetch places from Geoapify API.');
            }
        
            $places = $response->json()['features'];
            $cachedData = [];
        
            foreach ($places as $place) {
                $cachedData[] = [
                    'geoapifyId' => $place['properties']['place_id'] ?? null,
                    'name' => $place['properties']['name'] ?? 'Unnamed Place',
                    'longitude' => $place['geometry']['coordinates'][0] ?? null,
                    'latitude' => $place['geometry']['coordinates'][1] ?? null,
                ];
            }
        
            Cache::put('places', $cachedData, now()->addHours(24));
            $this->info('Places cached successfully.');
            $this->info(print_r($cachedData, true));
            return 0;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            $this->error($response->body());
            return 1;
        }
    }
}
