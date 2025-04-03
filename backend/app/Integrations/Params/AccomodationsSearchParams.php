<?php

namespace App\Integrations\Params;

use DateTimeImmutable;

class AccomodationsSearchParams {
    private string $airportIataCode;
    private DateTimeImmutable $checkInDate;
    private DateTimeImmutable $checkOutDate;
    private int $adultCount;
    private int $childCount;
    private int $infantCount;
    private int $roomCount;

    private function __construct(
        string $airportIataCode,
        string $checkInDate,
        string $checkOutDate,
        int $adultCount,
        int $childCount,
        int $infantCount,
        int $roomCount
    ) {
        $this->airportIataCode = $airportIataCode;
        $this->checkInDate = new DateTimeImmutable($checkInDate);
        $this->checkOutDate = new DateTimeImmutable($checkOutDate);
        $this->adultCount = $adultCount;
        $this->childCount = $childCount;
        $this->infantCount = $infantCount;
        $this->roomCount = $roomCount;
    }

    public function getAirportIataCode(): string {
        return $this->airportIataCode;
    }

    public function getCheckInDate(): DateTimeImmutable {
        return $this->checkInDate;
    }

    public function getCheckOutDate(): DateTimeImmutable {
        return $this->checkOutDate;
    }

    public function getRoomCount(): int {
        return $this->roomCount;
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
            $data['airportIataCode'] ?? null,
            $data['checkInDate'] ?? null,
            $data['checkOutDate'] ?? null,
            $data['adultCount'] ?? 1,
            $data['childCount'] ?? 0,
            $data['infantCount'] ?? 0,
            $data['roomCount'] ?? 1
        );
    }
}