<?php

namespace App\Integrations\Params;

use DateTimeImmutable;

class FlightsSearchParams {
    private string $departureAirportIataCode;
    private ?string $destinationAirportIataCode;
    private DateTimeImmutable $departureDate;
    private int $adultCount;
    private int $childCount;
    private int $infantCount;

    private function __construct(
        string $departureAirportIataCode,
        ?string $destinationAirportIataCode,
        string $departureDate,
        int $adultCount,
        int $childCount,
        int $infantCount,
    ) {
        $this->departureAirportIataCode = $departureAirportIataCode;
        $this->destinationAirportIataCode = $destinationAirportIataCode;
        $this->departureDate = new DateTimeImmutable($departureDate);
        $this->adultCount = $adultCount;
        $this->childCount = $childCount;
        $this->infantCount = $infantCount;
    }

    public function getDepartureAirportIataCode(): string {
        return $this->departureAirportIataCode;
    }

    public function getDestinationAirportIataCode(): string {
        return $this->destinationAirportIataCode;
    }

    public function getDepartureDate(): DateTimeImmutable {
        return $this->departureDate;
    }

    public function getAdultCount(): int {
        return $this->adultCount;
    }

    public function getChildCount(): int {
        return $this->childCount;
    }

    public function getInfantCount(): int {
        return $this->infantCount;
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['departureAirportIataCode'] ?? null,
            $data['destinationAirportIataCode'] ?? null,
            $data['departureDate'] ?? null,
            $data['adultCount'] ?? 1,
            $data['childCount'] ?? 0,
            $data['infantCount'] ?? 0,
        );
    }
}