import { createSelector } from '@reduxjs/toolkit';
import { RootState } from './store';
import { Flight, AccomodationWithOffers } from '../types/';

// Base selectors
const selectSearchState = (state: RootState) => state.search;


export const selectRawResults = createSelector(
  [selectSearchState],
  (search) => search.results
);
// Memoized selectors
export const selectSearchParams = createSelector(
  [selectSearchState],
  (search) => ({
    departureAirport: search.departureAirport,
    dateRange: search.dateRange,
    passengers: search.passengers
  })
);

export const selectRequestState = createSelector(
  [selectSearchState],
  (search) => search.requestState
);

export const selectSearchResults = createSelector(
  [selectSearchState],
  (search) => ({
    results: search.results,
    isLoading: search.requestState.status === 'loading',
    error: search.requestState.error
  })
);

export const selectNormalizedResults = createSelector(
  [selectRawResults],
  (results) => {
    if (!results) return null;
    
    const normalized: Array<{
      country: string;
      destination: string;
      departureFlights: Flight[];
      accomodations: AccomodationWithOffers[];
      returnFlights: Flight[];
    }> = [];
    
    for (const [country, destinations] of Object.entries(results)) {
      for (const [destination, data] of Object.entries(destinations)) {
        normalized.push({
          country,
          destination,
          accomodations: data.accomodations,
          departureFlights: data.departureFlights,
          returnFlights: data.returnFlights
        });
      }
    }
    
    return normalized;
  }
);

export const selectAvailableCountries = createSelector(
  [selectRawResults],
  (results) => results ? Object.keys(results) : []
);