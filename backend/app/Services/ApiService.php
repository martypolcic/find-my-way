<?php

namespace App\Services;

use App\Integrations\Params\SearchParams;
use App\Integrations\FlightsApi;
use ReflectionClass;
use App\Models\Provider;

class ApiService
{
    private array $apis = [];

    public function __construct()
    {
        $this->loadActiveApis();
    }

    private function loadActiveApis()
    {
        $activeApis = Provider::where('active', true)->get();

        foreach ($activeApis as $apiProvider) {
            $fullClassName = "App\\Integrations\\AirlineAPI\\" . $apiProvider->class_name;

            if (class_exists($fullClassName)) {
                $reflection = new ReflectionClass($fullClassName);

                if ($reflection->implementsInterface(FlightsApi::class)) {
                    $this->apis[] = app($fullClassName);
                }
            }
        }
    }

    public function searchFlights(SearchParams $searchParams)
    {
        foreach ($this->apis as $api) {
            $api->searchFlights($searchParams);
        }
    }
}

