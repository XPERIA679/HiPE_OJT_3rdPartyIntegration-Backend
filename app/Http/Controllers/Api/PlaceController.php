<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class PlaceController extends Controller
{
    public function generalList()
    {
        $places = Cache::get('places', []);

        $weatherData = array_map(function($place) {
            return $this->getWeatherData($place);
        }, $places);

        return response()->json($weatherData);
    }

    public function listByCurrentBaseWeather()
    {
        $places = $this->generalList()->original;

        $categorized = [];
        foreach ($places as $place) {
            $weather = $place['weather'][0]['main'];
            $categorized[$weather][] = $place;
        }

        return response()->json($categorized);
    }

    public function singleCurrentDetails($geoapifyId)
    {
        $places = Cache::get('places', []);
        $place = collect($places)->firstWhere('geoapifyId', $geoapifyId);

        if ($place) {
            return response()->json($this->getWeatherData($place));
        }

        return response()->json(['error' => 'Place not found'], 404);
    }

    public function singleGetFullDetails($geoapifyId)
    {
        $currentDetails = $this->singleCurrentDetails($geoapifyId)->original;
        $forecast = $this->getForecastData($currentDetails['latitude'], $currentDetails['longitude']);

        return response()->json([
            'current' => $currentDetails,
            'forecast' => $forecast
        ]);
    }

    private function getWeatherData($place)
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

    private function getForecastData($lat, $lon)
    {
        $apiKey = env('OPENWEATHER_API_KEY');
        $response = Http::get("https://api.openweathermap.org/data/2.5/forecast?lat=$lat&lon=$lon&appid=$apiKey&units=metric");

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }
}
