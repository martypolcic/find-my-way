type Airport = {
    id: number;
    iataCode: string;
    icaoCode: string;
    name: string;
    city: string;
    country: string;
    latitude: string;
    longitude: string;
};

type SearchState = {
    // Search parameters
    departureAirport: Airport | null;
    dateRange: [string | null, string | null];
    passengers: { adults: number; children: number; infants: number; rooms: number };

    // Request state
    requestState: {
        status: "idle" | "loading" | "succeeded" | "failed";
        error: string | null;
    };
    results: SearchResults | null;
};

export interface Flight {
    id: number;
    flightNumber: string | null;
    flightKey: string;
    departureDate: string;
    arrivalDate: string;
    departureAirportId: number;
    departureAirportIataCode: string;
    arrivalAirportId: number;
    arrivalAirportIataCode: string;
    airlineId: number | null;
    price_value: number;
    currency_code: string;
}

export interface AccommodationOffer {
    checkIn: string;
    checkOut: string;
    price: string;
    currency: string;
    description: string | null;
}

export interface Accommodation {
    id: string;
    name: string;
    description: string | null;
    priceLevel: string | null;
    latitude: string;
    longitude: string;
    provider: string;
}

export interface AccomodationWithOffers {
    accomodation: Accommodation;
    offers: AccommodationOffer[];
}

export interface DestinationResults {
    departureFlights: Flight[];
    accomodations: AccomodationWithOffers[];
    returnFlights: Flight[];
}

export interface CountryResults {
    [destination: string]: DestinationResults;
}

export interface SearchResults {
    [country: string]: CountryResults;
}

export interface SelectedFlight {
    id: number;
    flightKey: string;
    departure: string;
    arrival: string;
    departureTime: string;
    arrivalTime: string;
    price: number;
    currency: string;
}
  
export interface SelectedAccommodation {
    id: string;
    name: string;
    provider: string;
    price: number;
    currency: string;
    checkIn: string;
    checkOut: string;
}
  
export interface SelectionsState {
    destination: {
        country: string;
        city: string;
        availability: DestinationAvailability;
    } | null;
    departureFlight: SelectedFlight | null;
    returnFlight: SelectedFlight | null;
    accomodation: SelectedAccommodation | null;
}

export interface DestinationAvailability {
    hasDepartureFlights: boolean;
    hasReturnFlights: boolean;
    hasAccommodations: boolean;
}

export type { Airport, SearchState };