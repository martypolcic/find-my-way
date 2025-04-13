import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { SelectionsState, Flight, AccomodationWithOffers } from '../types';
import { DestinationAvailability } from '../types';

interface ExtendedSelectionsState extends SelectionsState {
  destination: {
    country: string;
    city: string;
    availability: DestinationAvailability;
  } | null;
}

const initialState: ExtendedSelectionsState = {
  destination: null,
  departureFlight: null,
  returnFlight: null,
  accomodation: null,
};

const selectionsSlice = createSlice({
  name: 'selections',
  initialState,
  reducers: {
    selectDestination: (
      state, 
      action: PayloadAction<{ 
        country: string; 
        city: string;
        departureFlights: Flight[];
        returnFlights: Flight[];
        accommodations: AccomodationWithOffers[];
      }>
    ) => {
      const { country, city, departureFlights, accommodations, returnFlights } = action.payload;
      
      state.destination = {
        country,
        city,
        availability: {
          hasDepartureFlights: departureFlights.length > 0,
          hasReturnFlights: returnFlights.length > 0,
          hasAccommodations: accommodations.length > 0
        }
      };
      state.departureFlight = null;
      state.returnFlight = null;
      state.accomodation = null;
    },
    selectDepartureFlight: (state, action: PayloadAction<Flight>) => {
      const flight = action.payload;
      state.departureFlight = {
        id: flight.id,
        flightKey: flight.flightKey,
        departure: flight.departureAirportIataCode,
        arrival: flight.arrivalAirportIataCode,
        departureTime: flight.departureDate,
        arrivalTime: flight.arrivalDate,
        price: flight.price_value,
        currency: flight.currency_code
      };
    },
    selectReturnFlight: (state, action: PayloadAction<Flight>) => {
      const flight = action.payload;
      state.returnFlight = {
        id: flight.id,
        flightKey: flight.flightKey,
        departure: flight.departureAirportIataCode,
        arrival: flight.arrivalAirportIataCode,
        departureTime: flight.departureDate,
        arrivalTime: flight.arrivalDate,
        price: flight.price_value,
        currency: flight.currency_code
      };
    },
    selectAccommodation: (state, action: PayloadAction<{ 
      accommodation: AccomodationWithOffers;
    }>) => {
      const { accommodation} = action.payload;
      // Find the lowest offer
      const lowestOffer = accommodation.offers.length > 0
        ? Math.min(...accommodation.offers.map(o => parseFloat(o.price)))
        : null;
      const offer = accommodation.offers.find(o => parseFloat(o.price) === lowestOffer);
      if (!offer) return;
      
      state.accomodation = {
        id: accommodation.accomodation.id,
        name: accommodation.accomodation.name,
        provider: accommodation.accomodation.provider,
        price: parseFloat(offer.price),
        currency: offer.currency,
        checkIn: offer.checkIn,
        checkOut: offer.checkOut
      };
    },
    clearSelections: (state) => {
      state.destination = null;
      state.departureFlight = null;
      state.returnFlight = null;
      state.accomodation = null;
    },
    clearFlights: (state) => {
      state.departureFlight = null;
      state.returnFlight = null;
    },
    clearAccommodation: (state) => {
      state.accomodation = null;
    },
    setDestinationAvailability: (
      state,
      action: PayloadAction<DestinationAvailability>
    ) => {
      if (state.destination) {
        state.destination.availability = action.payload;
      }
    }
  }
});

export const { 
  selectDestination,
  selectDepartureFlight,
  selectReturnFlight,
  selectAccommodation,
  clearSelections,
  clearFlights,
  clearAccommodation,
  setDestinationAvailability
} = selectionsSlice.actions;

export default selectionsSlice.reducer;