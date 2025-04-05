<?php
namespace App\Integrations;

use GuzzleHttp\Client as HttpClient;
use Generator;
use App\Models\City;

class OurAirportsApi {
    private readonly HttpClient $httpClient;
    private string $airportsUrl = 'https://davidmegginson.github.io/ourairports-data/airports.csv';
    private string $countriesUrl = 'https://davidmegginson.github.io/ourairports-data/countries.csv';

    public function __construct() {
        $this->httpClient = new HttpClient();
    }

    private function streamCsv(string $url): Generator {
        $response = $this->httpClient->get($url);
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $response->getBody());
        rewind($handle);

        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            yield array_combine($header, $row);
        }
        fclose($handle);
    }

    private function getCountryMap(): array {
        $countries = [];
        foreach ($this->streamCsv($this->countriesUrl) as $country) {
            $countries[$country['code']] = $country['name'];
        }
        return $countries;
    }

    public function processCity() {
        $countryMap = $this->getCountryMap();
        $batchSize = 1000;
        $batch = [];
        $existingCitys = $this->getExistingCities();

        foreach ($this->streamCsv($this->airportsUrl) as $cityData) {
            if (!$this->isValidCity($cityData)) continue;

            $cityEntry = [
                'name' => $cityData['municipality'],
                'country' => $countryMap[$cityData['iso_country']] ?? null,
                'latitude' => $cityData['latitude_deg'],
                'longitude' => $cityData['longitude_deg'],
            ];

            if (isset($existingCitys[$cityData['iata_code']])) {
                // Check if update is needed
                if ($this->needsUpdate($existingCitys[$cityData['name']], $cityEntry)) {
                    City::where('name', $cityData['name'])->update($cityEntry);
                }
            } else {
                // New airport, add to batch
                $batch[] = $cityEntry;
            }

            // Insert batch when limit is reached
            if (count($batch) >= $batchSize) {
                City::insert($batch);
                $batch = [];
            }
        }

        // Insert remaining batch
        if (!empty($batch)) {
            City::insert($batch);
        }
    }

    private function needsUpdate(array $existing, array $new): bool {
        return $existing['name'] !== $new['name']
            || $existing['country'] !== $new['country']
            || $existing['latitude'] !== $new['latitude']
            || $existing['longitude'] !== $new['longitude'];
    }

    /**
     * Fetches existing airports from database
     */
    private function getExistingCities(): array {
        return City::all()
                      ->keyBy('name')
                      ->toArray();
    }

    private function isValidCity(array $cityData): bool {
        return !empty($cityData['iata_code'])
            && str_contains($cityData['type'], 'airport') 
            && $cityData['scheduled_service'] === 'yes';
    }
}
