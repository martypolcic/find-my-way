<?php

namespace App\Services;

use App\Integrations\Params\AccomodationsSearchParams;
use App\Integrations\Params\FlightsSearchParams;
use App\Integrations\Params\TripsSearchParams;
use App\Integrations\FlightsApi;
use ReflectionClass;
use App\Models\Provider;
use App\Integrations\AccomodationApi;

class ApiService
{
    private array $flightApis = [];
    private array $accomodationApis = [];

    public function __construct()
    {
        $this->loadActiveFlightApis();
        $this->loadActiveAccomodationApis();
    }

    private function loadActiveFlightApis()
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

    private function loadActiveAccomodationApis()
    {
        $activeApis = Provider::where('active', true)->get();

        foreach ($activeApis as $apiProvider) {
            $fullClassName = "App\\Integrations\\AccomodationAPI\\" . $apiProvider->class_name;
            
            if (class_exists($fullClassName)) {
                $reflection = new ReflectionClass($fullClassName);

                if ($reflection->implementsInterface(AccomodationApi::class)) {
                    $this->accomodationApis[] = app($fullClassName);
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

    public function searchAccomodationOffers(AccomodationsSearchParams $searchParams)
    {
        foreach ($this->accomodationApis as $api) {
            if (method_exists($api, 'searchAccomodationOffers')) {
                $api->searchAccomodationOffers($searchParams);
            }
        }
    }
}

