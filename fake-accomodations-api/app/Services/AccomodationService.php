<?php
namespace App\Services;

use App\Models\Accomodation;
use App\Models\AccomodationBooking;
use App\Models\City;
use Illuminate\Support\Carbon;

class AccomodationService
{
    private const PRICE_PER_NIGHT = 60;

    public function generateAccomodationsForCity(City $city)
    {
        $existingAccomodations = $city->accomodations()->count();
        if ($existingAccomodations > 0) {
            return;
        }

        $accomodationsCount = rand(4, 15);
        for ($i = 0; $i < $accomodationsCount; $i++) {
            $rooms = rand(1, 3);
            
            $accomodation = Accomodation::create([
                'name' => $this->generateRandomName() . " in {$city->name}",
                'city_id' => $city->id,
                'latitude' => $city->latitude + rand(-1, 1) / 1000,
                'longitude' => $city->longitude + rand(-1, 1) / 1000,
                'capacity' => $rooms * 2,
                'rooms' => $rooms
            ]);
        }
    }

    private function generateRandomName()
    {
        $adjectives = ['Cozy', 'Luxurious', 'Charming', 'Modern', 'Spacious', 'Elegant', 'Rustic', 'Stylish', 'Comfortable', 'Trendy'];
        $nouns = ['Apartment', 'Suite', 'Room', 'Villa', 'Cabin', 'Lodge', 'House'];

        $randomAdjective = $adjectives[array_rand($adjectives)];
        $randomNoun = $nouns[array_rand($nouns)];

        return "{$randomAdjective} {$randomNoun}";
    }

    public function generateAccomodationBookings(string $city, string $checkInDate, string $checkOutDate)
    {
        $accomodations = Accomodation::query()
            ->whereHas('city', function ($query) use ($city) {
                $query->where('name', $city);
            })
            ->get();

        foreach ($accomodations as $accomodation) {
            $this->generateUnavailableBookings($accomodation, $checkInDate);
            $this->createOfferForAccomodation($accomodation, $checkInDate, $checkOutDate);
        }
    }

    private function createOfferForAccomodation(Accomodation $accomodation, string $checkInDate, string $checkOutDate)
    {
        $unavailableBookings = AccomodationBooking::query()
            ->where('accomodation_id', $accomodation->id)
            ->where('available', false)
            ->where(function ($query) use ($checkInDate, $checkOutDate) {
                $query->whereBetween('check_in_date', [$checkInDate, $checkOutDate])
                    ->orWhereBetween('check_out_date', [$checkInDate, $checkOutDate]);
            })
            ->count();
        
        $existingBooking = AccomodationBooking::query()
            ->where('accomodation_id', $accomodation->id)
            ->where('available', true)
            ->whereDate('check_in_date', $checkInDate)
            ->whereDate('check_out_date', $checkOutDate)
            ->count();
        
        if ($unavailableBookings > 0 || $existingBooking > 0) {
            return;
        }

        $checkIn = Carbon::createFromFormat('Y-m-d', $checkInDate);
        $checkOut = Carbon::createFromFormat('Y-m-d', $checkOutDate);

        AccomodationBooking::create([
            'accomodation_id' => $accomodation->id,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'total_price' => $this->generateAccomodationBookingPrice($checkIn->diffInDays($checkOut)),
            'available' => true,
            'currency' => 'EUR',
        ]);
    }

    private function generateUnavailableBookings(Accomodation $accomodation, string $dateInput)
    {
        $unavailableBookings = AccomodationBooking::query()
            ->where('accomodation_id', $accomodation->id)
            ->where('available', false)
            ->whereMonth('check_in_date', Carbon::createFromFormat('Y-m-d', $dateInput)->month)
            ->whereYear('check_in_date', Carbon::createFromFormat('Y-m-d', $dateInput)->year)
            ->count();
        if ($unavailableBookings > 0) {
            return;
        }

        $numberOfBookings = rand(1, 3);
        $daysInMonth = Carbon::createFromFormat('Y-m-d', $dateInput)->daysInMonth;
        $bookingSegment = $daysInMonth / $numberOfBookings;
        $offset = floor($bookingSegment / 2);

        for ($i = 1; $i <= $numberOfBookings; $i++) {
            $startDate = Carbon::createFromFormat('Y-m-d', $dateInput)->startOfMonth()->addDays($i * floor($bookingSegment / 2) + rand(-$offset, $offset));
            $endDate = $startDate->copy()->addDays(rand(1, 3));

            AccomodationBooking::create([
                'accomodation_id' => $accomodation->id,
                'check_in_date' => $startDate->toDateString(),
                'check_out_date' => $endDate->toDateString(),
                'total_price' => $this->generateAccomodationBookingPrice($startDate->diffInDays($endDate)),
                'available' => false,
                'currency' => 'EUR',
            ]);
        }
    }

    private function generateAccomodationBookingPrice($bookedDays)
    {
        $basePrice = self::PRICE_PER_NIGHT * $bookedDays;
        $randomFactor = $basePrice * (rand(-10, 10) / 100);

        return round($basePrice + $randomFactor, 2);
    }
}