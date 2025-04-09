<?php

namespace App\Integrations;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class AmadeusBaseApi {
    private HttpClient $httpClient;
    private ?string $apiToken = null;
    private bool $tokenRetry = false;

    public function __construct()
    {
        $this->httpClient = new HttpClient([
            'base_uri' => 'https://test.api.amadeus.com/',
        ]);
    }

    public static function getProvider(): string
    {
        return 'Amadeus';
    }

    private function requestNewToken(): void
    {
        $response = $this->httpClient->post('v1/security/oauth2/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => env('APP_AMADEUS_API_KEY'),
                'client_secret' => env('APP_AMADEUS_API_SECRET'),
            ],
        ]);

        $decoded = json_decode($response->getBody()->getContents(), true);
        $this->apiToken = $decoded['access_token'];
    }

    protected function makeRequest(string $method, string $endpoint, array $params = []): ?array
    {
        try {
            $response = $this->httpClient->request($method, $endpoint, [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiToken}",
                    'Accept' => 'application/json',
                ],
                'query' => $params,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 401 && !$this->tokenRetry) {
                $this->requestNewToken();
                $this->tokenRetry = true;
                return $this->makeRequest($method, $endpoint, $params);
            }
            //TODO: Log error
            return null;
        } catch (ServerException $e) {
            //TODO: Log error
            return null;
        } finally {
            $this->tokenRetry = false;
        }
    }

    protected function makeAsyncRequest(string $method, string $endpoint, array $params = []): \GuzzleHttp\Promise\PromiseInterface
    {
        if ($this->apiToken === null) {
            $this->requestNewToken();
        }
        
        return $this->httpClient->requestAsync($method, $endpoint, [
            'headers' => [
                'Authorization' => "Bearer {$this->apiToken}",
                'Accept' => 'application/json',
            ],
            'query' => $params,
        ]);
    }
}