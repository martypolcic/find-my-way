<?php

namespace App\Services;

use App\Integrations\FlightsApi;
use App\Integrations\Params\AccomodationsSearchParams;
use App\Integrations\Params\FlightsSearchParams;
use App\Integrations\Params\TripsSearchParams;
use App\Models\ProviderService;
use ReflectionClass;
use App\Integrations\AccomodationSearch;
use App\Integrations\AccomodationOffersSearch;

class ApiService
{
    private array $flightApis = [];
    private array $accomodationApis = [];
    private array $accomodationOfferApis = [];

    public function __construct()
    {
        $this->loadActiveFlightApis();
        $this->loadActiveAccomodationApis();
    }

    private function loadActiveFlightApis()
    {
        $activeApis = ProviderService::where('active', true)->get();

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
        $activeApis = ProviderService::where('active', true)->get();

        foreach ($activeApis as $apiProvider) {
            $fullClassName = "App\\Integrations\\AccomodationAPI\\" . $apiProvider->class_name;

            if (class_exists($fullClassName)) {
                $reflection = new ReflectionClass($fullClassName);

                if ($reflection->implementsInterface(AccomodationSearch::class)) {
                    $this->accomodationApis[] = app($fullClassName);
                }
                if ($reflection->implementsInterface(AccomodationOffersSearch::class)) {
                    $this->accomodationOfferApis[] = app($fullClassName);
                }
            }
        }
    }

    public function searchAccomodations(AccomodationsSearchParams $accomodationSearchParams)
    {
        $airport = AirportService::getAirportByIata($accomodationSearchParams->getAirportIataCode());
        if (!$airport) {
            throw new \Exception("Airport not found");
        }

        $requests = [];
        foreach ($this->accomodationApis as $api) {
            if (method_exists($api, 'searchAccomodationsAsync')) {
                $requests[] = [
                    'api' => $api,
                    'params' => $accomodationSearchParams,
                    'type' => 'accomodation'
                ];
            }
        }

        $this->executeConcurrentRequests($requests);
    }

    public function searchFlights(FlightsSearchParams $flightSearchParams)
    {
        $departureAirport = AirportService::getAirportByIata($flightSearchParams->getDepartureAirportIataCode());
        if (!$departureAirport) {
            throw new \Exception("Departure airport not found");
        }

        $requests = [];
        foreach ($this->flightApis as $api) {
            $requests[] = [
                'api' => $api,
                'params' => $flightSearchParams,
                'type' => 'flight'
            ];
        }

        $this->executeConcurrentRequests($requests);
    }

    public function searchTrips(TripsSearchParams $tripSearchParams)
    {
        $departureAirport = AirportService::getAirportByIata($tripSearchParams->getDepartureAirportIataCode());
        if (!$departureAirport) {
            throw new \Exception("Departure airport not found");
        }

        $requests = $this->prepareOutboundFlightRequests($tripSearchParams, $departureAirport);
        $this->executeConcurrentRequests($requests);


        $destinationAirportIds = FlightService::getDestinationIds(
            $departureAirport->id,
            $tripSearchParams->getDepartureDate()->format('Y-m-d')
        );
        $requests = $this->prepareReturnFlightRequests($tripSearchParams, $destinationAirportIds);
        $requests = array_merge($requests, $this->prepareAccomodationsRequests($tripSearchParams, $destinationAirportIds));
        $this->executeConcurrentRequests($requests);


        $requests = $this->prepareAccomodationOffersRequests($tripSearchParams, $destinationAirportIds);
        $this->executeConcurrentRequests($requests);
    }

    private function prepareOutboundFlightRequests(TripsSearchParams $tripSearchParams): array
    {
        $requests = [];

        $flightSearchParams = FlightsSearchParams::fromArray([
            'departureAirportIataCode' => $tripSearchParams->getDepartureAirportIataCode(),
            'destinationAirportIataCode' => null,
            'departureDate' => $tripSearchParams->getDepartureDate()->format('Y-m-d'),
            'adultCount' => $tripSearchParams->getAdultCount(),
            'childCount' => $tripSearchParams->getChildCount(),
            'infantCount' => $tripSearchParams->getInfantCount(),
        ]);
        
        foreach ($this->flightApis as $api) {
            $requests[] = [
                'api' => $api,
                'params' => $flightSearchParams,
                'type' => 'flight'
            ];
        }

        return $requests;
    }

    private function prepareReturnFlightRequests(TripsSearchParams $tripSearchParams, $destinationAirportIds): array
    {
        $requests = [];

        foreach ($destinationAirportIds as $destinationAirportId) {
            $destinationAirport = AirportService::getAirportById($destinationAirportId);

            $returnFlightsParams = FlightsSearchParams::fromArray([
                'departureAirportIataCode' => $destinationAirport->iata_code,
                'destinationAirportIataCode' => null,
                'departureDate' => $tripSearchParams->getReturnDate()->format('Y-m-d'),
                'adultCount' => $tripSearchParams->getAdultCount(),
                'childCount' => $tripSearchParams->getChildCount(),
                'infantCount' => $tripSearchParams->getInfantCount(),
            ]);

            foreach ($this->flightApis as $api) {
                $requests[] = [
                    'api' => $api,
                    'params' => $returnFlightsParams,
                    'type' => 'flight'
                ];
            }
        }

        return $requests;
    }

    private function prepareAccomodationsRequests(TripsSearchParams $tripSearchParams, $destinationAirportIds): array
    {
        $requests = [];

        foreach ($destinationAirportIds as $destinationAirportId) {
            $destinationAirport = AirportService::getAirportById($destinationAirportId);

            $accomodationParams = AccomodationsSearchParams::fromArray([
                'airportIataCode' => $destinationAirport->iata_code,
                'checkInDate' => $tripSearchParams->getDepartureDate()->format('Y-m-d'),
                'checkOutDate' => $tripSearchParams->getReturnDate()->format('Y-m-d'),
                'adultCount' => $tripSearchParams->getAdultCount(),
                'childCount' => $tripSearchParams->getChildCount(),
                'infantCount' => $tripSearchParams->getInfantCount(),
                'roomCount' => $tripSearchParams->getRoomCount(),
            ]);

            foreach ($this->accomodationApis as $api) {
                if (method_exists($api, 'searchAccomodationsAsync')) {
                    $requests[] = [
                        'api' => $api,
                        'params' => $accomodationParams,
                        'type' => 'accomodation'
                    ];
                }
            }
        }

        return $requests;
    }

    private function prepareAccomodationOffersRequests(TripsSearchParams $tripSearchParams, $destinationAirportIds): array
    {
        $requests = [];

        foreach ($destinationAirportIds as $destinationAirportId) {
            $destinationAirport = AirportService::getAirportById($destinationAirportId);

            $accomodationParams = AccomodationsSearchParams::fromArray([
                'airportIataCode' => $destinationAirport->iata_code,
                'checkInDate' => $tripSearchParams->getDepartureDate()->format('Y-m-d'),
                'checkOutDate' => $tripSearchParams->getReturnDate()->format('Y-m-d'),
                'adultCount' => $tripSearchParams->getAdultCount(),
                'childCount' => $tripSearchParams->getChildCount(),
                'infantCount' => $tripSearchParams->getInfantCount(),
                'roomCount' => $tripSearchParams->getRoomCount(),
            ]);

            foreach ($this->accomodationOfferApis as $api) {
                if (method_exists($api, 'searchAccomodationOffersAsync')) {
                    $requests[] = [
                        'api' => $api,
                        'params' => $accomodationParams,
                        'type' => 'accomodationOffer'
                    ];
                }
            }
        }

        return $requests;
    }

    private function executeConcurrentRequests(array $requests)
    {
        $promises = [];
        foreach ($requests as $requestData) {
            $api = $requestData['api'];
            $params = $requestData['params'];
            $type = $requestData['type'];

            $result = match($type) {
                'flight' => $api->searchFlightsAsync($params),
                'accomodation' => $api->searchAccomodationsAsync($params),
                'accomodationOffer' => $api->searchAccomodationOffersAsync($params),
                default => null
            };

            if (is_array($result)) {
                $promises = array_merge($promises, $result);
            } elseif ($result instanceof \GuzzleHttp\Promise\PromiseInterface) {
                $promises[] = $result;
            }
        }
        \GuzzleHttp\Promise\Utils::all($promises)->wait();
    }
}

