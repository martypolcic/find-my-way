<?php

namespace App\Integrations\AccomodationAPI;

use App\Models\Provider;
use App\Services\AccomodationService;

use App\Integrations\Params\AccomodationsSearchParams;
use App\Integrations\TokenBucketRateLimiter;
use App\Models\Accomodation;
use App\Services\AirportService;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\Log;
use App\Exceptions\DailyLimitExceededException;
use App\Integrations\AccomodationOffersSearch;
use App\Integrations\AccomodationSearch;
use App\Models\ProviderService;
use App\Services\ProviderServiceService;

class TripadvisorApi implements AccomodationSearch, AccomodationOffersSearch
{
    use TokenBucketRateLimiter;
    private readonly HttpClient $httpClient;

    public function __construct()
    {
        $this->httpClient = new HttpClient([
            'base_uri' => 'https://api.content.tripadvisor.com/api/v1/location/',
        ]);
        $this->setRateLimit(10, 30);
    }

    public static function getProvider(): string
    {
        return 'Tripadvisor';
    }

    private function processAccomodationResponse(?array $response, AccomodationsSearchParams $searchParams): void
    {
        if (!$response || empty($response['data'])) return;

        $providerId = Provider::where('name', self::getProvider())->first()->id;
        $airport = AirportService::getAirportByIata($searchParams->getAirportIataCode());

        foreach ($response['data'] as $accomodation) {
            $accomodationData = [
                'external_id' => $accomodation['location_id'],
                'name' => $accomodation['name'],
                'airport_id' => $airport->id,
                'latitude' => null,
                'longitude' => null,
                'provider_id' => $providerId,
                'price_level' => null,
                'description' => null,
            ];

            AccomodationService::createOrUpdateAccomodation($accomodationData);
        }
    }

    public function searchAccomodationsAsync(AccomodationsSearchParams $searchParams, int $retryCount = 0): \GuzzleHttp\Promise\PromiseInterface
    {
        $airport = AirportService::getAirportByIata($searchParams->getAirportIataCode());
        $providerId = Provider::where('name', self::getProvider())->first()->id;
        if (AccomodationService::checkIfAccomodationsExist($airport, $providerId)) {
            return \GuzzleHttp\Promise\Create::promiseFor(null);
        }

        $this->throttle();

        $promise = $this->httpClient->getAsync('search', [
            'query' => [
                'key' => config('services.tripadvisor.api_key'),
                'searchQuery' => $airport->city,
                'category' => 'hotels',
                'radius' => 50,
                'radiusUnit' => 'km',
            ],
        ]);

        return $promise->then(
            function ($response) use ($searchParams) {
                $data = json_decode($response->getBody()->getContents(), true);
                $this->processAccomodationResponse($data, $searchParams);
            },
            function ($error) {
                // Log error
                return null;
            }
        );
    }

    private function processAccomodationOffersResponse(?array $response, AccomodationsSearchParams $searchParams): void
    {
        if (!$response || empty($response)) return;
        
        $providerId = Provider::where('name', self::getProvider())->first()->id;
        $airport = AirportService::getAirportByIata($searchParams->getAirportIataCode());

        $accomodationData = [
            'external_id' => $response['location_id'],
            'name' => $response['name'],
            'airport_id' => $airport->id,
            'latitude' => $response['latitude'] ?? null,
            'longitude' => $response['longitude'] ?? null,
            'provider_id' => $providerId,
            'price_level' => $response['price_level'] ?? null,
            'description' => $response['description'] ?? null,
        ];

        AccomodationService::createOrUpdateAccomodation($accomodationData);
    }
    
    public function searchAccomodationOffersAsync(AccomodationsSearchParams $searchParams, int $retryCount = 0): array
    {
        $destinationAirport = AirportService::getAirportByIata($searchParams->getAirportIataCode());
        $accomodationExternalIds = AccomodationService::getExternalIdList(self::getProvider(), $destinationAirport->id);
   
        $existing = Accomodation::query()
            ->whereIn('external_id', $accomodationExternalIds)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->pluck('external_id')
            ->toArray();
        $filteredIds = array_diff($accomodationExternalIds, $existing);


        $promises = [];

        foreach (array_chunk($filteredIds, 10) as $batch) {
            $batchPromises = $this->processBatchWithRateLimiting($batch, $searchParams, $retryCount);
            $promises = array_merge($promises, $batchPromises);
            
            usleep(50000); // 50ms
        }

        return $promises;
    }

    private function processBatchWithRateLimiting(
        array $externalIds,
        AccomodationsSearchParams $searchParams,
        int $retryCount
    ): array {
        $promises = [];
        
        foreach ($externalIds as $externalId) {
            try {
                $this->throttle();
                
                $promises[] = $this->httpClient->getAsync($externalId . '/details', [
                    'query' => [
                        'key' => config('services.tripadvisor.api_key'),
                        'language' => 'en',
                        'currency' => 'EUR',
                    ],
                    'timeout' => 30
                ])->then(
                    function ($response) use ($searchParams) {
                        $data = json_decode($response->getBody()->getContents(), true);
                        $this->processAccomodationOffersResponse($data, $searchParams);
                        return $data;
                    },
                    function ($exception) use ($searchParams, $retryCount, $externalId) {

                        return $this->handleRequestError(
                            $exception,
                            $searchParams,
                            $retryCount,
                            $externalId
                        );
                    }
                );
            } catch (DailyLimitExceededException $e) {
                throw $e;
            } catch (\Exception $e) {
                Log::error('Request failed', [
                    'external_id' => $externalId,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }
        
        return $promises;
    }

    private function handleRequestError(
        $exception,
        AccomodationsSearchParams $searchParams,
        int $retryCount,
        string $externalId
    ) {
        $response = $exception->getResponse();
        $statusCode = $response ? $response->getStatusCode() : null;
        
        if ($statusCode === 429) {
            $errorBody = $response ? json_decode($response->getBody()->getContents(), true) : null;
            $errorMessage = $errorBody['message'] ?? 'Unknown error';
            
            // Case 1: Daily limit exceeded
            if ($errorMessage === 'Limit Exceeded') {
                Log::error('Daily API limit exceeded', [
                    'external_id' => $externalId,
                    'message' => $errorMessage
                ]);
                $providerService = new ProviderServiceService();
                $providerServiceId = ProviderService::where('provider_id', Provider::where('name', self::getProvider())->first()->id)
                    ->where('type', 'accomodation')
                    ->first()
                    ->id;
                $providerService->dissableProviderService($providerServiceId);
                return null;
            }
            // Case 2: Rate limit exceeded (too many requests per second)
            elseif ($errorMessage === 'Too many requests') {
                if ($retryCount < 3) {
                    Log::warning('Rate limit exceeded, retrying', [
                        'external_id' => $externalId,
                        'retry_count' => $retryCount,
                        'message' => $errorMessage
                    ]);
                    
                    // Exponential backoff
                    $backoffTime = min(pow(2, $retryCount) * 100000, 5000000);
                    usleep($backoffTime);
                    
                    return $this->processBatchWithRateLimiting(
                        [$externalId],
                        $searchParams,
                        $retryCount + 1
                    );
                } else {
                    Log::error('Max retries reached for rate limited request', [
                        'external_id' => $externalId,
                        'retry_count' => $retryCount
                    ]);
                }
            }
        }
        
        // For all other errors
        Log::error('Accomodation API error', [
            'external_id' => $externalId,
            'error' => $exception->getMessage(),
            'code' => $exception->getCode()
        ]);
        
        return null;
    }
}