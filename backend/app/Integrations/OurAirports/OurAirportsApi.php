<?php
namespace App\Integrations\OurAirports;

use GuzzleHttp\Client as HttpClient;
use Generator;
use App\Models\Airport;

class OurAirportsApi {
    private readonly HttpClient $httpClient;
    private string $airportsUrl = 'https://davidmegginson.github.io/ourairports-data/airports.csv';
    private string $countriesUrl = 'https://davidmegginson.github.io/ourairports-data/countries.csv';

    public function __construct() {
        $this->httpClient = new HttpClient();
    }

    /**
     * Streams CSV file from URL and returns a generator
     */
    private function streamCsv(string $url): Generator {
        $response = $this->httpClient->get($url);
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $response->getBody());
        rewind($handle);

        $header = fgetcsv($handle); // Read header

        while (($row = fgetcsv($handle)) !== false) {
            yield array_combine($header, $row); // Return row as an associative array
        }
        fclose($handle);
    }

    /**
     * Reads and maps country codes to names
     */
    private function getCountryMap(): array {
        $countries = [];
        foreach ($this->streamCsv($this->countriesUrl) as $country) {
            $countries[$country['code']] = $country['name'];
        }
        return $countries;
    }

    /**
     * Parses airports, filters them, compares & updates database
     */
    public function processAirports() {
        $countryMap = $this->getCountryMap();
        $batchSize = 1000;
        $batch = [];
        $existingAirports = $this->getExistingAirports();

        foreach ($this->streamCsv($this->airportsUrl) as $airportData) {
            if (!$this->isValidAirport($airportData)) continue;

            $airportEntry = [
                'iata_code' => $airportData['iata_code'],
                'icao_code' => !empty($airportData['icao_code']) ? $airportData['icao_code'] : null,
                'name' => $airportData['name'],
                'city' => $airportData['municipality'],
                'country' => $countryMap[$airportData['iso_country']] ?? null,
                'latitude' => $airportData['latitude_deg'],
                'longitude' => $airportData['longitude_deg'],
                'active' => false,
            ];

            if (isset($existingAirports[$airportData['iata_code']])) {
                // Check if update is needed
                if ($this->needsUpdate($existingAirports[$airportData['iata_code']], $airportEntry)) {
                    Airport::where('iata_code', $airportData['iata_code'])->update($airportEntry);
                }
            } else {
                // New airport, add to batch
                $batch[] = $airportEntry;
            }

            // Insert batch when limit is reached
            if (count($batch) >= $batchSize) {
                Airport::insert($batch);
                $batch = [];
            }
        }

        // Insert remaining batch
        if (!empty($batch)) {
            Airport::insert($batch);
        }
    }

    /**
     * Checks if airport data has changed
     */
    private function needsUpdate(array $existing, array $new): bool {
        return $existing['icao_code'] !== $new['icao_code']
            || $existing['name'] !== $new['name']
            || $existing['city'] !== $new['city']
            || $existing['country'] !== $new['country']
            || $existing['latitude'] != $new['latitude']
            || $existing['longitude'] != $new['longitude'];
    }

    /**
     * Fetches existing airports from database
     */
    private function getExistingAirports(): array {
        return Airport::all()
                      ->keyBy('iata_code')
                      ->toArray();
    }

    /**
     * Filters valid airports
     */
    private function isValidAirport(array $airportData): bool {
        return !empty($airportData['iata_code'])
            && str_contains($airportData['type'], 'airport');
    }
}
