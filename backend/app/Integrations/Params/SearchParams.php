<?php

namespace App\Integrations\Params;

use App\Integrations\Api;
use App\Models\Flight;
use DateTimeImmutable;
use Generator;
use GuzzleHttp\Client as HttpClient;

class SearchParams {
    private string $departureAirportIataCode;
    private DateTimeImmutable $departureDate;
    private int $passengerCount;

    private function __construct(
        string $departureAirportIataCode,
        string $departureDate,
        int $passengerCount
    ) {
        $this->departureAirportIataCode = $departureAirportIataCode;
        $this->departureDate = new DateTimeImmutable($departureDate);
        $this->passengerCount = $passengerCount;
    }

    public function getDepartureAirportIataCode(): string {
        return $this->departureAirportIataCode;
    }

    public function getDepartureDate(): DateTimeImmutable {
        return $this->departureDate;
    }

    public function getPassengerCount(): int {
        return $this->passengerCount;
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['departureAirportIataCode'] ?? null,
            $data['departureDate'] ?? null,
            $data['passengerCount'] ?? null
        );
    }
}