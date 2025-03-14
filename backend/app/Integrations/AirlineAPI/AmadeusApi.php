<?php

namespace App\Integrations\AirlineAPI;

use App\Integrations\Api;
use App\Integrations\Params\SearchParams;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use App\Services\AirportService;
use App\Models\Airline;
use App\Models\Provider;
use App\Services\FlightService;
use App\Services\FlightPriceService;

class AmadeusApi implements Api {
    private HttpClient $httpClient;
    private HttpClient $httpClientToken;
    private ?string $apiToken = null;
    private bool $tokenRetry = false;

    public function __construct()
    {
        $this->httpClient = new HttpClient([
            'base_uri' => 'https://test.api.amadeus.com/v1/shopping/',
        ]);

        $this->httpClientToken = new HttpClient([
            'base_uri' => 'https://test.api.amadeus.com/v1/security/oauth2/',
        ]);
    }

    public static function getProvider(): string
    {
        return 'Amadeus';
    }

    private function getAirlineIataCode(): string
    {
        return 'AM';
    }

    /**
     * Requests and stores a new API token
     */
    private function requestNewToken(): void
    {
        $response = $this->httpClientToken->post('token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => env('APP_AMADEUS_API_KEY'),
                'client_secret' => env('APP_AMADEUS_API_SECRET'),
            ],
        ]);

        $decoded = json_decode($response->getBody()->getContents(), true);
        $this->apiToken = $decoded['access_token'];
    }

    /**
     * Fetches flight data from Amadeus API.
     * 
     * @param SearchParams $searchParams
     * @return array|null
     */
    private function fetchDestinations(SearchParams $searchParams): ?array
    {
        if (!$this->apiToken) {
            $this->requestNewToken();
        }

        $departureDate = $searchParams->getDepartureDate()->format('Y-m-d');

        try {
            $response = $this->httpClient->get('flight-destinations', [
                'query' => [
                    'origin' => $searchParams->getDepartureAirportIataCode(),
                    'departureDate' => $departureDate,
                    'oneWay' => 'true',
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiToken,
                ],
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientException $e) {
            // Unauthorized
            if ($e->getResponse()->getStatusCode() === 401 && !$this->tokenRetry) {
                $this->tokenRetry = true;
                $this->requestNewToken();
                return $this->fetchDestinations($searchParams);
            }
            return null;
        } catch (ServerException $e) {
            // Departure airport not supported by Amadeus
            // TODO: Log the error properly
        } finally {
            $this->tokenRetry = false;
        }

        return null;
    }

    private function fetchFlightDetails($origin, $destination, $date, $adults)
    {
        try {
            $response = $this->httpClient->get('flight-offers', [
                'query' => [
                    'originLocationCode' => $origin,
                    'destinationLocationCode' => $destination,
                    'departureDate' => $date,
                    'adults' => $adults,
                    'nonStop' => false,
                    'max' => 1,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            return null;
        }
    }

    public function searchFlights(SearchParams $searchParams)
    {
        $response = $this->fetchDestinations($searchParams);
        if (!$response || empty($response['data'])) return;

        $departureAirportId = AirportService::getAirportIdByIata($searchParams->getDepartureAirportIataCode());
        $airlineId = Airline::where('iata_code', self::getAirlineIataCode())->first()?->id;
        $providerId = Provider::where('name', self::getProvider())->first()->id;

        foreach ($response['data'] as $destination) {
            $flightData = [
                'flight_number' => null,
                'flight_key' => 'AMADEUS~' . $destination['origin'] . '~' . $destination['destination'] . '~' . $destination['departureDate'],
                'departure_date' => $destination['departureDate'],
                'arrival_date' => null,
                'departure_airport_id' => $departureAirportId,
                'arrival_airport_id' => AirportService::getAirportIdByIata($destination['destination']),
                'price_value' => $destination['price']['total'],
                'currency_code' => 'EUR',
                'airline_id' => $airlineId,
                'provider_id' => $providerId,
            ];

            $flight = FlightService::createOrUpdateFlight($flightData);
            FlightPriceService::storeFlightPrice($flight->id, $destination['price']['total'], 'EUR');
        }
    }
}