<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class WeatherService
{
    public function getCompanyData($cvr)
    {
        // User agent som CVR apien kræver
        $userAgent = 'VejrService - internship-task - Jens Bech Lauritsen +45 31317132';

        // Kald til CVR-apien, for at få lokation/bynavn
        $response = Http::withHeaders([
            'User-Agent' => $userAgent
        ])->get('https://cvrapi.dk/api', [
            'vat' => $cvr,
            'country' => 'dk'
        ]);

        
        if ($response->failed()) {
            return [];
        }

        return $response->json();
    }

    public function getWeatherData($city)
    {
        // Brug kun første del af bynavnet, så vi undgår bynavne som ikke eksisterer i vejr-apien
        $location = urlencode(explode(' ', $city)[0]);
        $response = Http::get("http://vejr.eu/api.php", [
            'location' => $location,
            'degree' => 'C'
        ]);
    
        Log::info("Anmoder om vejrdata for byen '$city' to API.", [
            'location' => $location
        ]);
    
        if ($response->failed()) {
            Log::error("API kaldet til vejrdatasystemet mislykkedes for byen '$location'", [
                'status' => $response->status(),
                'city' => $location,
                'response' => $response->body(), 
            ]);
            return [];
        }
    
        // Debugging: Log svaret fra vejrdatasystemet
        $weatherData = $response->json();
        Log::info("Vejrdata respons '$city':", [
            'data' => $weatherData,
        ]);
    
        // Tjek om vi modtog data fra vejr-API
        if (!isset($weatherData['CurrentData'])) {
            Log::error("Ingen vejrinformationer fundet for byen: $city", [
                'response' => $weatherData,
            ]);
            return [];
        }
    
        return $weatherData;
    }
    
    
    
    
    
}
