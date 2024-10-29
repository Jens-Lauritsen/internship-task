<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Services\WeatherService;
use Illuminate\Routing\Controller as BaseController;

class WeatherController extends BaseController
{
    protected $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    public function getWeather(Request $request)
    {
        $cvr = $request->query('cvr');

        // Hent virksomhedsdata via CVR API
        $companyData = $this->weatherService->getCompanyData($cvr);

        if (!isset($companyData['city'])) {
            Log::info("Invalid CVR or city not found", ['cvr' => $cvr]);
            return response()->json(['success' => false, 'message' => 'Invalid CVR or city not found'], 400);
        }

        $city = $companyData['city'];
        Log::info("Bynavn: " . $city, []);

        $weatherData = $this->weatherService->getWeatherData($city);

        // Tjek om vi modtog data fra vejr-API
        if (!$weatherData || !isset($weatherData['CurrentData'])) {
            Log::error("Ingen vejrinformationer fundet for byen: $city", []);
            return response()->json(['success' => false, 'message' => 'Weather data not found for city: ' . $city], 500);
        }

        // Returner data i JSON format
        return response()->json([
            'success' => true,
            'data' => [
                'location' => $weatherData['LocationName'], // Byens navn
                'temperature' => $weatherData['CurrentData']['temperature'] ?? 'N/A',
                'skyText' => $weatherData['CurrentData']['skyText'] ?? 'N/A',
                'humidity' => $weatherData['CurrentData']['humidity'] ?? 'N/A',
                'windText' => $weatherData['CurrentData']['windText'] ?? 'N/A'
            ]
        ]);
    }
}
