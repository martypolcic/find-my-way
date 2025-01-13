<?php 
namespace App\Integrations\AviationEdge;

use GuzzleHttp\Client as HttpClient;
use Generator;
use App\Models\Airport;

class AviationEdgeAirports {
    private readonly HttpClient $httpClient;

    public function __construct() {
        $this->httpClient = new HttpClient([
            'base_uri' => 'https://aviation-edge.com/v2/public/',
        ]);
    }

    // TODO: ryanair results can be paginated, we should handle that
    private function fetchAviationEdgeAirports(): Generator {
        $apiKey = env('APP_AVIATION_EDGE_API_KEY');
        $response = $this->httpClient->get('airportDatabase', [
            'query' => [
                'key' => $apiKey,
            ],
        ]);
        
        $decoded = json_decode($response->getBody()->getContents(), true);

        foreach($decoded as $airportData) {
            $airport = $this->transformAirport($airportData);
            if ($airport->isValid())
                yield $airport;
        }
    }

    private function transformAirport(array $airportData)
    {
        $airport = new Airport();

        $airport->iata_code = $airportData['codeIataAirport'];
        $airport->airport_name = $airportData['nameAirport'];
        $airport->country_name = $airportData['nameCountry'];
        $airport->latitude_deg = $airportData['latitudeAirport'];
        $airport->longitude_deg = $airportData['longitudeAirport'];

        return $airport;
    }

    public function updateAviationAirports() {
        foreach($this->fetchAviationEdgeAirports() as $airport) {
            Airport::updateOrCreate(
                ['iata_code' => $airport->iata_code],
                $airport->toArray()
            );
        }
    }
}