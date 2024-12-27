<?php 
namespace App\Integrations\OurAirports;

use GuzzleHttp\Client as HttpClient;
use Generator;
use App\Models\Airport;
use Exception;

class OurAirports {
    private readonly array $airports;
    private readonly array $regions;
    private readonly array $countries;
    private readonly HttpClient $httpClient;

    public function __construct() {
        $this->httpClient = new HttpClient([
            'base_uri' => 'https://davidmegginson.github.io/ourairports-data/',
        ]);

        $airportsFile = $this->httpClient->get('airports.csv');
        $countrysFile = $this->httpClient->get('countries.csv');


        $this->airports = $this->filterAirports($this->parseCsv($airportsFile->getBody()));
        $this->countries = $this->parseCsv($countrysFile->getBody());
    }

    private function parseCsv($csv) {
        $lines = explode("\n", $csv);
        $header = str_getcsv(array_shift($lines));
        $data = [];
    
        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }
            $fields = str_getcsv($line);
            $data[] = array_combine($header, $fields);
        }
    
        return $data;
    }

    private function filterAirports($airports) {
        return array_filter($airports, function($airport) {
            return $airport['iata_code'] !== '' &&
            strpos($airport['type'], 'airport') !== false &&
            $airport['latitude_deg'] !== '' &&
            $airport['longitude_deg'] !== '' &&
            $airport['iso_country'] !== '' &&
            $airport['municipality'] !== '';
        });
    }

    private function getCountryName($isoCountry) {
        foreach($this->countries as $country) {
            if ($country['code'] === $isoCountry) {
                return $country['name'];
            }
        }
        return null;
    }

    private function fetchOurAirports(): Generator {
        foreach($this->airports as $airportData) {
            $airport = $this->transformAirport($airportData);
            if ($airport->isValid()) {
                yield $airport;
            }
        }
    }

    private function transformAirport(array $airportData)
    {
        $airport = new Airport();

        $airport->iata_code = $airportData['iata_code'];
        $airport->airport_name = $airportData['name'];
        $airport->country_name = $this->getCountryName($airportData['iso_country']);
        $airport->city_name = $airportData['municipality'];
        $airport->latitude_deg = $airportData['latitude_deg'];
        $airport->longitude_deg = $airportData['longitude_deg'];
        return $airport;
    }

    public function updateAirports() {
        foreach($this->fetchOurAirports() as $airport) {
            Airport::updateOrCreate(
                ['iata_code' => $airport->iata_code],
                $airport->toArray()
            );
        }
    }
}