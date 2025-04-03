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

    public function searchTrips(TripsSearchParams $tripSearchParams)
    {
        $departureAirport = AirportService::getAirportByIata($tripSearchParams->getDepartureAirportIataCode());
        if (!$departureAirport) {
            //TODO: LOG and throw error response
            throw new \Exception("Departure airport not found");
        }

        $departureFlightsParams = FlightsSearchParams::fromArray([
            'departureAirportIataCode' => $departureAirport->iata_code,
            'destinationAirportIataCode' => null,
            'departureDate' => $tripSearchParams->getDepartureDate()->format('Y-m-d'),
            'adultCount' => $tripSearchParams->getAdultCount(),
            'childCount' => $tripSearchParams->getChildCount(),
            'infantCount' => $tripSearchParams->getInfantCount(),
        ]);

        foreach ($this->flightApis as $api) {
            $api->searchFlights($departureFlightsParams);
        }

        $destinationAirportIds = FlightService::getDestinationIds(
            $departureAirport->id,
            $tripSearchParams->getDepartureDate()->format('Y-m-d')
        );
        
        foreach ($destinationAirportIds as $destinationAirportId) {
            $destinationAirport = AirportService::getAirportById($destinationAirportId);

            $returnFlightsParams = FlightsSearchParams::fromArray([
                'departureAirportIataCode' => $destinationAirport->iata_code,
                'destinationAirportIataCode' => $departureAirport->iata_code,
                'departureDate' => $tripSearchParams->getReturnDate()->format('Y-m-d'),
                'adultCount' => $tripSearchParams->getAdultCount(),
                'childCount' => $tripSearchParams->getChildCount(),
                'infantCount' => $tripSearchParams->getInfantCount(),
            ]);

            foreach ($this->flightApis as $api) {
                $api->searchFlights($returnFlightsParams);
            }

            $accomodationParams = AccomodationsSearchParams::fromArray([
                'airportIataCode' => $destinationAirport->iata_code,
                'checkInDate' => $tripSearchParams->getDepartureDate()->format('Y-m-d'),
                'checkOutDate' => $tripSearchParams->getReturnDate()->format('Y-m-d'),
                'adultCount' => $tripSearchParams->getAdultCount(),
                'childCount' => $tripSearchParams->getChildCount(),
                'infantCount' => $tripSearchParams->getInfantCount(),
                'roomCount' => 1,
            ]);

            foreach ($this->accomodationApis as $api) {
                if (method_exists($api, 'searchAccomodationOffers')) {
                    $api->searchAccomodationOffers($accomodationParams);
                }
            }
        }
    }
}

