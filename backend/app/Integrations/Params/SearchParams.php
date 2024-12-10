<?php

namespace App\Integrations\Params;

use DateTimeImmutable;

class SearchParams {
    private string $departureAirportIataCode;
    private DateTimeImmutable $departureDate;
    private int $passengerCount;

    private function __construct(
        string $departureAirportIataCode,
        string $departureDate,
        string|int $passengerCount // TODO: Do this the right way, does laravel convert string to int ?
    ) {
        $this->departureAirportIataCode = $departureAirportIataCode;
        $this->departureDate = new DateTimeImmutable($departureDate);
        $this->passengerCount = (int) $passengerCount;
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