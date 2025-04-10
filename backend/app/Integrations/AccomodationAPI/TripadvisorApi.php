<?php

namespace App\Integrations\AccomodationAPI;

use App\Models\Provider;
use App\Services\AccomodationService;
use App\Integrations\Params\AccomodationsSearchParams;
use App\Models\Accomodation;
use App\Services\AirportService;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use App\Exceptions\DailyLimitExceededException;
use App\Integrations\AccomodationOffersSearch;
use App\Integrations\AccomodationSearch;
use App\Models\ProviderService;
use App\Services\TokenBucketRateLimiter;

class TripadvisorApi implements AccomodationSearch, AccomodationOffersSearch
{
    private readonly HttpClient $httpClient;

    public function __construct()
    {
        $this->httpClient = new HttpClient([
            'base_uri' => 'https://api.content.tripadvisor.com/api/v1/location/',
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
    }

    public static function getProvider(): string
    {
        return 'Tripadvisor';
    }

    protected function throttle(): void
    {
        TokenBucketRateLimiter::for($this->getProvider(), 10, 30)->throttle();
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

        return $this->httpClient->getAsync('search', [
            'query' => [
                'key' => config('services.tripadvisor.api_key'),
                'searchQuery' => $airport->city,
                'category' => 'hotels',
                'radius' => 50,
                'radiusUnit' => 'km',
            ],
        ])->then(
            function ($response) use ($searchParams) {
                $data = json_decode($response->getBody()->getContents(), true);
                $this->processAccomodationResponse($data, $searchParams);
                return $data;
            },
            function ($error) {
                $this->logError($error);
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
            try {
                $batchPromises = $this->processBatchWithRateLimiting($batch, $searchParams, $retryCount);
                $promises = array_merge($promises, $batchPromises);
                
                usleep(50000); // 50ms delay between batches
            } catch (DailyLimitExceededException $e) {
                Log::error('Daily limit exceeded', ['exception' => $e]);
                
            } catch (\Exception $e) {
                $this->logError($e, [
                    'context' => 'batch_processing'
                ]);
                continue;
            }

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
                        return $this->handleRequestError($exception, $searchParams, $retryCount, $externalId);
                    }
                );
            } catch (DailyLimitExceededException $e) {
                throw $e;
            } catch (\Exception $e) {
                $this->logError($e, [
                    'external_id' => $externalId,
                    'context' => 'batch_processing'
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
        if (!$exception instanceof RequestException) {
            $this->logError($exception, [
                'external_id' => $externalId,
                'context' => 'request_error'
            ]);
            return null;
        }

        $response = $exception->getResponse();
        $statusCode = $response ? $response->getStatusCode() : null;
        $errorBody = $response ? json_decode($response->getBody()->getContents(), true) : null;
        $errorMessage = $errorBody['message'] ?? 'Unknown error';

        switch ($statusCode) {
            case 429:
                return $this->handleRateLimitError($errorMessage, $externalId, $searchParams, $retryCount);
                
            case 403:
                if (str_contains($errorMessage, 'Limit Exceeded')) {
                    return $this->handleDailyLimitExceeded($externalId);
                }
                break;
                
            default:
                $this->logError($exception, [
                    'external_id' => $externalId,
                    'status_code' => $statusCode,
                    'error_message' => $errorMessage,
                    'context' => 'api_error'
                ]);
        }

        return null;
    }

    private function handleRateLimitError(
        string $errorMessage,
        string $externalId,
        AccomodationsSearchParams $searchParams,
        int $retryCount
    ) {
        if ($retryCount >= 3) {
            $this->logError(new \Exception('Max retries reached'), [
                'external_id' => $externalId,
                'retry_count' => $retryCount,
                'error_message' => $errorMessage
            ]);
            return null;
        }

        $backoffTime = min(pow(2, $retryCount) * 100000, 5000000);
        usleep($backoffTime);

        Log::warning('Rate limit exceeded - retrying', [
            'external_id' => $externalId,
            'retry_count' => $retryCount + 1,
            'wait_time_ms' => $backoffTime / 1000
        ]);

        return $this->processBatchWithRateLimiting(
            [$externalId],
            $searchParams,
            $retryCount + 1
        );
    }

    private function handleDailyLimitExceeded(string $externalId)
    {
        Log::error('Daily API limit exceeded', ['external_id' => $externalId]);
        
        $provider = Provider::where('name', self::getProvider())->first();
        if ($provider) {
            ProviderService::where('provider_id', $provider->id)
                ->where('service_type', 'accommodation')
                ->update(['active' => false]);
        }

        throw new DailyLimitExceededException('Daily API limit reached');
    }

    private function logError(\Throwable $exception, array $context = [])
    {
        Log::error($exception->getMessage(), array_merge([
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ], $context));
    }
}