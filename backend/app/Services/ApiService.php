<?php

namespace App\Services;

use App\Integrations\Params\SearchParams;
use App\Integrations\Params\FlightsSearchParams;
use App\Integrations\Params\TripsSearchParams;
use App\Integrations\FlightsApi;
use ReflectionClass;
use App\Models\Provider;

class ApiService
{
    private array $flightApis = [];

    public function __construct()
    {
        $this->loadActiveFlightApis();
    }

    private function loadActiveApis()
    {
        $activeApis = Provider::where('active', true)->get();

        foreach ($activeApis as $apiProvider) {
            $fullClassName = "App\\Integrations\\AirlineAPI\\" . $apiProvider->class_name;

            if (class_exists($fullClassName)) {
                $reflection = new ReflectionClass($fullClassName);

                if ($reflection->implementsInterface(FlightsApi::class)) {
                    $this->flightApis[] = app($fullClassName);
                }
            }
        }
    }

    public function searchFlights(FlightsSearchParams $searchParams)
    {
        foreach ($this->flightApis as $api) {
            $api->searchFlights($searchParams);
        }
    }
}

