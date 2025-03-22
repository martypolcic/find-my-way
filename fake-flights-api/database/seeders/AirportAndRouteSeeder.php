<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AirportAndRouteSeeder extends Seeder
{
    public function run(): void
    {
        // Insert sample airports
        DB::table('airports')->insert([
            ['iata_code' => 'VIE', 'icao_code' => 'LOWW', 'name' => 'Vienna International Airport', 'city' => 'Vienna', 'country' => 'Austria'],
            ['iata_code' => 'BTS', 'icao_code' => 'LZIB', 'name' => 'M. R. Štefánik Airport', 'city' => 'Bratislava', 'country' => 'Slovakia'],
            ['iata_code' => 'MAD', 'icao_code' => 'LEMD', 'name' => 'Adolfo Suárez Madrid–Barajas Airport', 'city' => 'Madrid', 'country' => 'Spain'],
            ['iata_code' => 'VLC', 'icao_code' => 'LEVC', 'name' => 'Valencia Airport', 'city' => 'Valencia', 'country' => 'Spain'],
            ['iata_code' => 'CRL', 'icao_code' => 'EBCI', 'name' => 'Brussels South Charleroi Airport', 'city' => 'Charleroi', 'country' => 'Belgium'],
            ['iata_code' => 'LHR', 'icao_code' => 'EGLL', 'name' => 'London Heathrow Airport', 'city' => 'London', 'country' => 'United Kingdom'],
            ['iata_code' => 'CDG', 'icao_code' => 'LFPG', 'name' => 'Charles de Gaulle Airport', 'city' => 'Paris', 'country' => 'France'],
            ['iata_code' => 'FRA', 'icao_code' => 'EDDF', 'name' => 'Frankfurt Airport', 'city' => 'Frankfurt', 'country' => 'Germany'],
            ['iata_code' => 'AMS', 'icao_code' => 'EHAM', 'name' => 'Amsterdam Schiphol Airport', 'city' => 'Amsterdam', 'country' => 'Netherlands'],
            ['iata_code' => 'BCN', 'icao_code' => 'LEBL', 'name' => 'Barcelona–El Prat Airport', 'city' => 'Barcelona', 'country' => 'Spain'],
            ['iata_code' => 'MUC', 'icao_code' => 'EDDM', 'name' => 'Munich Airport', 'city' => 'Munich', 'country' => 'Germany'],
            ['iata_code' => 'ATH', 'icao_code' => 'LGAV', 'name' => 'Athens International Airport', 'city' => 'Athens', 'country' => 'Greece'],
            ['iata_code' => 'OTP', 'icao_code' => 'LROP', 'name' => 'Henri Coandă International Airport', 'city' => 'Bucharest', 'country' => 'Romania'],
            ['iata_code' => 'DUB', 'icao_code' => 'EIDW', 'name' => 'Dublin Airport', 'city' => 'Dublin', 'country' => 'Ireland'],
            ['iata_code' => 'ZRH', 'icao_code' => 'LSZH', 'name' => 'Zurich Airport', 'city' => 'Zurich', 'country' => 'Switzerland'],
        ]);

        // Insert only **one-way** routes (no duplicates)
        $routes = [
            ['VIE', 'MAD', 180], ['VIE', 'VLC', 165], ['VIE', 'CRL', 110], ['VIE', 'LHR', 130], ['VIE', 'CDG', 140],
            ['BTS', 'MAD', 185], ['BTS', 'VLC', 170], ['BTS', 'CRL', 100], ['BTS', 'FRA', 85],
            ['MAD', 'VLC', 60], ['MAD', 'CRL', 155], ['MAD', 'AMS', 150],
            ['VLC', 'CRL', 140], ['VLC', 'BCN', 50],
            ['CRL', 'CDG', 60],
            ['LHR', 'BCN', 120], ['LHR', 'DUB', 90], ['LHR', 'ZRH', 95], ['LHR', 'AMS', 60],
            ['CDG', 'BCN', 110], ['CDG', 'ZRH', 70], ['CDG', 'DUB', 110], ['CDG', 'OTP', 165],
            ['FRA', 'MAD', 150], ['FRA', 'MUC', 50], ['FRA', 'ATH', 180], ['FRA', 'AMS', 75],
            ['AMS', 'DUB', 105], ['AMS', 'MUC', 90], ['AMS', 'ATH', 190], ['AMS', 'OTP', 170],
            ['BCN', 'OTP', 165], ['BCN', 'ZRH', 90], ['BCN', 'MUC', 120],
            ['MUC', 'OTP', 160], ['MUC', 'ATH', 145], ['MUC', 'ZRH', 55], ['MUC', 'DUB', 130],
            ['ATH', 'OTP', 75], ['ATH', 'ZRH', 155], ['ATH', 'CDG', 175],
            ['OTP', 'DUB', 185], ['OTP', 'FRA', 165], ['OTP', 'AMS', 170],
            ['DUB', 'MUC', 130], ['DUB', 'CDG', 110],
            ['ZRH', 'FRA', 55], ['ZRH', 'OTP', 175], ['ZRH', 'BCN', 90],
        ];

        foreach ($routes as $route) {
            DB::table('routes')->insert([
                'departure_airport' => $route[0],
                'arrival_airport' => $route[1],
                'duration' => $route[2],
            ]);
        }
    }
}
