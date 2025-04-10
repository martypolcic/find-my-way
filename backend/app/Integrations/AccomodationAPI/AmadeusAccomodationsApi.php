<?php

namespace App\Integrations\AccomodationAPI;

use App\Integrations\AccomodationOffersSearch;
use App\Integrations\AccomodationSearch;
use App\Integrations\AmadeusBaseApi;
use App\Integrations\Params\AccomodationsSearchParams;
use App\Models\Provider;
use App\Services\AccomodationOfferService;
use App\Services\AccomodationService;
use App\Services\AirportService;
use App\Services\TokenBucketRateLimiter;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class AmadeusAccomodationsApi extends AmadeusBaseApi implements AccomodationOffersSearch, AccomodationSearch
{
    private const ERROR_CODE_UNABLE_TO_PROCESS = 11;
    private const ERROR_CODE_URI_TOO_LONG = 703;
    private const ERROR_CODE_INVALID_PROPERTY_CODE = 1257;
    private const ERROR_CODE_VERIFY_PROVIDER = 1351;
    private const ERROR_CODE_NO_ROOMS = 3664;
    private const ERROR_CODE_PROPERTY_CODE_NOT_FOUND = 3237;
    private const ERROR_CODE_RATE_NOT_AVAILABLE = 3494;
    private const ERROR_CODE_MISSING_DATA = 10604;
    private const ERROR_CODE_ROOM_RATE_NOT_FOUND = 11226;

    private const MAX_PROPERTY_IDS = 10;
    private const MAX_URI_LENGTH = 2000;
    private const MAX_RETRIES = 3;

    public static function getProvider(): string
    {
        return 'Amadeus';
    }

    protected function throttle(): void
    {
        TokenBucketRateLimiter::for($this->getProvider(), 10, 10)->throttle();
    }

    public function searchAccomodationOffersAsync(
        AccomodationsSearchParams $searchParams,
        array $hotelIds = null,
        int $retryCount = 0
    ): \GuzzleHttp\Promise\PromiseInterface 
    {
        $destinationAirport = AirportService::getAirportByIata($searchParams->getAirportIataCode());
        
        // Get hotel IDs if not provided
        $hotelIds = $hotelIds ?? AccomodationService::getExternalIdList(self::getProvider(), $destinationAirport->id);
        //take only the first 10 hotel ids
        $hotelIds = array_slice($hotelIds, 0, self::MAX_PROPERTY_IDS);

        
        if (empty($hotelIds)) {
            return \GuzzleHttp\Promise\Create::promiseFor(null);
        }

        // Split hotel IDs into chunks to avoid URI length limits
        $hotelChunks = $this->splitHotelIds($hotelIds);

        // Process first chunk immediately, queue others
        $firstChunk = array_shift($hotelChunks);
        $promise = $this->makeHotelOffersRequest($firstChunk, $searchParams, $retryCount);

        // Chain remaining chunks
        foreach ($hotelChunks as $chunk) {
            $promise = $promise->then(
                function () use ($chunk, $searchParams) {
                    return $this->makeHotelOffersRequest($chunk, $searchParams);
                },
                function ($error) {
                    $this->logError($error);
                    return null;
                }
            );
        }

        return $promise;
    }

    private function makeHotelOffersRequest(
        array $hotelIds,
        AccomodationsSearchParams $searchParams,
        int $retryCount = 0
    ): \GuzzleHttp\Promise\PromiseInterface {
        $queryParams = [
            'hotelIds' => implode(',', $hotelIds),
            'checkInDate' => $searchParams->getCheckInDate()->format('Y-m-d'),
            'checkOutDate' => $searchParams->getCheckOutDate()->format('Y-m-d'),
            'adults' => $searchParams->getAdultCount(),
            'roomQuantity' => $searchParams->getRoomCount(),
            'includeClosed' => false,
            'currency' => 'EUR',
            'bestRateOnly' => false,
        ];

        $this->throttle();

        return $this->makeAsyncRequest('GET', 'v3/shopping/hotel-offers', $queryParams)
            ->then(
                function ($response) use ($searchParams) {
                    $data = json_decode($response->getBody()->getContents(), true);
                    Log::debug('sucessfull accomodation offer response');
                    $this->processAccomodationOfferResponse($data, $searchParams);
                    return $data;
                },
                function ($error) use ($searchParams, $hotelIds, $retryCount) {
                    return $this->handleOfferError($error, $searchParams, $hotelIds, $retryCount);
                }
            );
    }

    private function splitHotelIds(array $hotelIds): array
    {
        // First split by maximum hotels per request
        $chunks = array_chunk($hotelIds, self::MAX_PROPERTY_IDS);
        
        // Further split chunks that would exceed URI length
        $finalChunks = [];
        foreach ($chunks as $chunk) {
            $uriLength = $this->estimateUriLength($chunk);
            
            if ($uriLength <= self::MAX_URI_LENGTH) {
                $finalChunks[] = $chunk;
                continue;
            }
            
            // If still too long, split into smaller chunks
            $finalChunks = array_merge(
                $finalChunks,
                $this->splitToMaxUriLength($chunk)
            );
        }
        
        return $finalChunks;
    }

    private function estimateUriLength(array $hotelIds): int
    {
        // Base URI length without hotel IDs
        $baseUri = strlen('https://test.api.amadeus.com/v3/shopping/hotel-offers?')
            + strlen('&checkInDate=YYYY-MM-DD')
            + strlen('&checkOutDate=YYYY-MM-DD')
            + strlen('&adults=2')
            + strlen('&roomQuantity=1')
            + strlen('&includeClosed=0')
            + strlen('&currency=EUR')
            + strlen('&bestRateOnly=1');
        
        // Add hotel IDs length
        $hotelsLength = strlen(implode(',', $hotelIds)) + strlen('hotelIds=');
        
        return $baseUri + $hotelsLength;
    }

    private function splitToMaxUriLength(array $hotelIds): array
    {
        $result = [];
        $currentChunk = [];
        $currentLength = 0;
        
        foreach ($hotelIds as $hotelId) {
            $addedLength = strlen($hotelId) + 1; // +1 for comma
            
            if (($currentLength + $addedLength) > self::MAX_URI_LENGTH) {
                $result[] = $currentChunk;
                $currentChunk = [];
                $currentLength = 0;
            }
            
            $currentChunk[] = $hotelId;
            $currentLength += $addedLength;
        }
        
        if (!empty($currentChunk)) {
            $result[] = $currentChunk;
        }
        
        return $result;
    }

    private function handleOfferError(
        $error,
        AccomodationsSearchParams $searchParams,
        array $hotelIds,
        int $retryCount
    ): ?\GuzzleHttp\Promise\PromiseInterface {
        if (!$error instanceof RequestException || !$error->hasResponse()) {
            $this->logError($error);
            return null;
        }

        $response = $error->getResponse();
        $errorData = json_decode($response->getBody()->getContents(), true);

        // Check for specific errors
        if (isset($errorData['errors'][0]['code'])) {
            $errorCode = $errorData['errors'][0]['code'];
            
            if ($errorCode === self::ERROR_CODE_URI_TOO_LONG) {
                return $this->handleUriTooLongError($searchParams, $hotelIds, $retryCount);
            }
            
            if (in_array($errorCode, [self::ERROR_CODE_UNABLE_TO_PROCESS, self::ERROR_CODE_PROPERTY_CODE_NOT_FOUND, self::ERROR_CODE_ROOM_RATE_NOT_FOUND, self::ERROR_CODE_NO_ROOMS, self::ERROR_CODE_INVALID_PROPERTY_CODE, self::ERROR_CODE_MISSING_DATA, self::ERROR_CODE_RATE_NOT_AVAILABLE, self::ERROR_CODE_VERIFY_PROVIDER])) {
                return $this->handleInvalidHotels($errorData, $searchParams, $hotelIds, $retryCount);
            }
        }

        $this->logError($error, [
            'error_data' => $errorData,
            'hotel_ids' => $hotelIds,
            'retry_count' => $retryCount
        ]);
        
        return null;
    }

    private function handleUriTooLongError(
        AccomodationsSearchParams $searchParams,
        array $hotelIds,
        int $retryCount
    ): ?\GuzzleHttp\Promise\PromiseInterface {
        if ($retryCount >= self::MAX_RETRIES) {
            $this->logError(new \Exception('Max retries reached for URI length error'), [
                'hotel_ids' => $hotelIds
            ]);
            return null;
        }

        // Split into smaller chunks and retry
        $smallerChunks = $this->splitToMaxUriLength($hotelIds);
        
        if (count($smallerChunks) === 1 && count($smallerChunks[0]) === count($hotelIds)) {
            // Couldn't split further
            $this->logError(new \Exception('Cannot split hotel IDs further'), [
                'hotel_ids' => $hotelIds
            ]);
            return null;
        }

        Log::warning('Retrying with smaller hotel ID chunks due to URI length limit', [
            'original_count' => count($hotelIds),
            'new_chunks' => array_map('count', $smallerChunks),
            'retry_count' => $retryCount + 1
        ]);

        // Process the first chunk immediately
        $firstChunk = array_shift($smallerChunks);
        $promise = $this->makeHotelOffersRequest($firstChunk, $searchParams, $retryCount + 1);

        // Chain remaining chunks
        foreach ($smallerChunks as $chunk) {
            $promise = $promise->then(
                function () use ($chunk, $searchParams, $retryCount) {
                    return $this->makeHotelOffersRequest($chunk, $searchParams, $retryCount + 1);
                },
                function ($error) {
                    $this->logError($error);
                    return null;
                }
            );
        }

        return $promise;
    }

    private function handleInvalidHotels(
        array $errorData,
        AccomodationsSearchParams $searchParams,
        array $hotelIds,
        int $retryCount
    ): ?\GuzzleHttp\Promise\PromiseInterface {
        if ($retryCount >= self::MAX_RETRIES) {
            $this->logError(new \Exception('Max retries reached'), [
                'error_data' => $errorData,
                'hotel_ids' => $hotelIds
            ]);
            return null;
        }

        // Extract faulty hotel IDs from error message
        $faultyHotelIds = $this->extractFaultyHotelIds($errorData);
        
        if (empty($faultyHotelIds)) {
            $this->logError(new \Exception('Could not extract faulty hotel IDs'), [
                'error_data' => $errorData
            ]);
            return null;
        }

        // Remove faulty hotels and retry
        $validHotelIds = array_diff($hotelIds, $faultyHotelIds);
        
        if (empty($validHotelIds)) {
            $this->logError(new \Exception('No valid hotels left after filtering'), [
                'original_hotel_ids' => $hotelIds,
                'faulty_hotel_ids' => $faultyHotelIds
            ]);
            return null;
        }

        // Log the retry attempt
        Log::warning('Retrying with filtered hotel IDs', [
            'remaining_hotels' => $validHotelIds,
            'removed_hotels' => $faultyHotelIds,
            'retry_count' => $retryCount + 1
        ]);

        // Retry with remaining hotels
        return $this->searchAccomodationOffersAsync(
            $searchParams,
            $validHotelIds,
            $retryCount + 1
        );
    }

    private function extractFaultyHotelIds(array $errorData): array
    {
        if (!isset($errorData['errors'][0]['source']['parameter'])) {
            return [];
        }

        $paramString = $errorData['errors'][0]['source']['parameter'];
        
        if (preg_match('/hotelIds=([^,]+(,[^,]+)*)/', $paramString, $matches)) {
            return explode(',', $matches[1]);
        }

        return [];
    }

    private function logError(\Throwable $error, array $context = []): void
    {
        Log::error('Amadeus API Error: ' . $error->getMessage(), array_merge([
            'exception' => get_class($error),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'provider' => self::getProvider(),
        ], $context));
    }

    private function processAccomodationOfferResponse(?array $response, AccomodationsSearchParams $searchParams): void
    {
        if (!$response || empty($response['data'])) return;

        foreach ($response['data'] as $accomodationOffer) {
            foreach ($accomodationOffer['offers'] as $offer) {
                $accomodationOfferData = [
                    'accomodation_id' => AccomodationService::getAccomodationIdByExternalId($accomodationOffer['hotel']['hotelId'], self::getProvider()),
                    'external_id' => $offer['id'],
                    'check_in' => $offer['checkInDate'],
                    'check_out' => $offer['checkOutDate'],
                    'price' => $offer['price']['total'],
                    'currency' => $offer['price']['currency'],
                    'description' => $offer['room']['description']['text'] ?? null,
                ];
                AccomodationOfferService::createOrUpdateAccomodationOffer($accomodationOfferData);
            }
        }
    }

    private function processAccomodationReponse(?array $response, AccomodationsSearchParams $searchParams): void
    {
        if (!$response || empty($response['data'])) return;

        $providerId = Provider::where('name', self::getProvider())->first()->id;
        $airport = AirportService::getAirportByIata($searchParams->getAirportIataCode());

        foreach ($response['data'] as $accomodation) {
            $accomodationData = [
                'external_id' => $accomodation['hotelId'],
                'name' => $accomodation['name'],
                'airport_id' => $airport->id,
                'latitude' => $accomodation['geoCode']['latitude'],
                'longitude' => $accomodation['geoCode']['longitude'],
                'provider_id' => $providerId,
                'price_level' => null,
                'description' => null,
            ];

            AccomodationService::createOrUpdateAccomodation($accomodationData);
        }
    }

    public function searchAccomodationsAsync(AccomodationsSearchParams $searchParams): \GuzzleHttp\Promise\PromiseInterface
    {
        $providerId = Provider::where('name', self::getProvider())->first()->id;
        $destinationAirport = AirportService::getAirportByIata($searchParams->getAirportIataCode());
        
        // TODO : Check if this does what I want it to
        if (AccomodationService::checkIfAccomodationsExist($destinationAirport, $providerId)) {
            return \GuzzleHttp\Promise\Create::promiseFor(null);
        }

        $this->throttle();
        
        $promise = $this->makeAsyncRequest('GET', 'v1/reference-data/locations/hotels/by-city', [
            'cityCode' => $destinationAirport->iata_code,
            'radius' => 50,
            'radiusUnit' => 'KM',
        ]);

        return $promise->then(
            function ($response) use ($searchParams) {
                $data = json_decode($response->getBody()->getContents(), true);
                $this->processAccomodationReponse($data, $searchParams);
            },
            function ($error) {
                // Handle error
                return null;
            }
        );
    }
}