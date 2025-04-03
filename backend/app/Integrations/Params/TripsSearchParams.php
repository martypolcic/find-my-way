<?php

namespace App\Integrations\Params;

use DateTimeImmutable;

class TripsSearchParams {
    private string $departureAirportIataCode;
    private DateTimeImmutable $departureDate;
    private DateTimeImmutable $returnDate;
    private int $adultCount;
    private int $childCount;
    private int $infantCount;
    private int $roomCount;

    private function __construct(
        string $departureAirportIataCode,
        string $departureDate,
        string $returnDate,
        int $adultCount,
        int $childCount,
        int $infantCount,
        int $roomCount
    ) {
        $this->departureAirportIataCode = $departureAirportIataCode;
        $this->departureDate = new DateTimeImmutable($departureDate);
        $this->returnDate = new DateTimeImmutable($returnDate);
        $this->adultCount = $adultCount;
        $this->childCount = $childCount;
        $this->infantCount = $infantCount;
        $this->roomCount = $roomCount;
    }

    public function getDepartureAirportIataCode(): string {
        return $this->departureAirportIataCode;
    }

    public function getDepartureDate(): DateTimeImmutable {
        return $this->departureDate;
    }

    public function getReturnDate(): DateTimeImmutable {
        return $this->returnDate;
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

    public function getRoomCount(): int {
        return $this->roomCount;
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['departureAirportIataCode'] ?? null,
            $data['departureDate'] ?? null,
            $data['returnDate'] ?? null,
            $data['adultCount'] ?? 1,
            $data['childCount'] ?? 0,
            $data['infantCount'] ?? 0,
            $data['roomCount'] ?? 1
        );
    }
}