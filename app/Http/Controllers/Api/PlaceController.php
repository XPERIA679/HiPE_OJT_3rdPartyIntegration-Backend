<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;

class PlaceController extends Controller
{
    /**
     * Retrieves a list of places from the cache and retrieves weather data for each place.
     */
    public function generalList(): JsonResponse
    {
        $places = Cache::get('places', []);

        $weatherData = array_map(function($place) {
            return $this->getWeatherData($place);
        }, $places);

        return response()->json($weatherData);
    }

    /**
     * Retrieves a list of places from the cache and categorizes them by current base weather.
     */
    public function listByCurrentBaseWeather(): JsonResponse
    {
        $places = $this->generalList()->original;

        $categorized = [];
        foreach ($places as $place) {
            $weather = $place['weather'][0]['main'];
            $categorized[$weather][] = $place;
        }

        return response()->json($categorized);
    }

    /**
     * Retrieves the current details of a single place based on its Geoapify ID.
     */
    public function singleCurrentDetails(string $geoapifyId): JsonResponse
    {
        $places = Cache::get('places', []);
        $place = collect($places)->firstWhere('geoapifyId', $geoapifyId);

        if ($place) {
            return response()->json($this->getWeatherData($place));
        }

        return response()->json(['error' => 'Place not found'], 404);
    }

    /**
     * Retrieves the full details of a single place including current details and forecast based on Geoapify ID.
     */
    public function singleGetFullDetails(string $geoapifyId): JsonResponse
    {
        $currentDetails = $this->singleCurrentDetails($geoapifyId)->original;
        $forecast = $this->getForecastData($currentDetails['latitude'], $currentDetails['longitude']);

        return response()->json([
            'current' => $currentDetails,
            'forecast' => $forecast
        ]);
    }

    /**
     * Retrieves weather data for a given place using the OpenWeatherMap API.
     */
    private function getWeatherData($place): ?array
    {
        $apiKey = env('OPENWEATHER_API_KEY');
        $response = Http::get("https://api.openweathermap.org/data/2.5/weather?lat={$place['latitude']}&lon={$place['longitude']}&appid=$apiKey&units=metric");

        if ($response->successful()) {
            $place['weather'] = $response->json()['weather'];
            $place['main'] = $response->json()['main'];
            return $place;
        }

        return null;
    }

    /**
     * Retrieves the forecast data for a given latitude and longitude using the OpenWeatherMap API.
     */
    private function getForecastData(float $lat, float $lon): ?array
    {
        $apiKey = env('OPENWEATHER_API_KEY');
        $response = Http::get("https://api.openweathermap.org/data/2.5/forecast?lat=$lat&lon=$lon&appid=$apiKey&units=metric");

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    public function currentLocation(Request $request)
    {
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        Cache::put('user_location', [
            'latitude' => $latitude,
            'longitude' => $longitude
        ], now()->addDay());

        return response()->json([
            'latitude' => $latitude,
            'longitude' => $longitude
        ], 200);
    }
}
