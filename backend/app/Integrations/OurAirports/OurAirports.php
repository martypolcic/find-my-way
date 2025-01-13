<?php 
namespace App\Integrations\OurAirports;

use GuzzleHttp\Client as HttpClient;
use App\Models\Airport;
use LogicException;
use Generator;
use App\Integrations\OurAirports\OurAirportsValidator;

use function PHPUnit\Framework\isEmpty;

class OurAirports {
    private readonly array $airports;
    private readonly array $countries;
    private readonly HttpClient $httpClient;

    public function __construct() {
        $this->httpClient = new HttpClient([
            'base_uri' => 'https://davidmegginson.github.io/ourairports-data/',
        ]);
    }

    private function fetchCountries() {
        $response = $this->httpClient->get('countries.csv');
        $csv = $response->getBody()->getContents();
        $lines = explode("\n", $csv);
        $data = [];
        // Skip the first (header) line and last (extra \n) line
        for ($i=1; $i < count($lines) - 1; $i++) {
            $parts = str_getcsv($lines[$i]);
            $data[$parts[1]] = $parts[2];
        }

        $this->countries = $data;
    }

    private function getCountryByCode($code) {
        $countryName = $this->countries[$code] ?? null;
        if ($countryName === null)
            throw new LogicException("Country with code $code not found");

        return $countryName;
    }

    /**
     * @return array<Airport>
     */
    public function getAirports(): Generator {
        $this->fetchCountries();

        return $this->fetchOurAirports();
    }

    private function fetchOurAirports(): Generator {
        $validator = new OurAirportsValidator();

        $response = $this->httpClient->get('airports.csv');
        $csv = $response->getBody()->getContents();
        $lines = explode("\n", $csv);

        for ($i=1; $i < count($lines) - 1; $i++) { 
            $parts = str_getcsv($lines[$i]);

            $airportData = [
                'iata_code' => $parts[13],
                'name' => $parts[3],
                'iso_country' => $this->getCountryByCode($parts[8]),
                'municipality' => $parts[10],
                'latitude_deg' => $parts[4],
                'longitude_deg' => $parts[5],
                'scheduled_service' => $parts[11],
                'type' => $parts[2],
            ];

            if($validator->validate($airportData)) {
                yield $this->transformAirport($airportData);
            }
        }
    }

    private function transformAirport(array $airportData)
    {
        $airport = new Airport();

        $airport->iata_code = $airportData['iata_code'];
        $airport->airport_name = $airportData['name'];
        $airport->country_name = $airportData['iso_country'];
        $airport->city_name = $airportData['municipality'];
        $airport->latitude_deg = $airportData['latitude_deg'];
        $airport->longitude_deg = $airportData['longitude_deg'];
        return $airport;
    }

    public function updateAirports() {
        $airports = $this->getAirports();
        foreach ($airports as $airport) {
            $airport->save();
        }
    }
}