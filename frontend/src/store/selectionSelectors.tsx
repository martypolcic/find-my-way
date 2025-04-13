import { createSelector } from '@reduxjs/toolkit';
import { RootState } from './store';

const selectSelectionsState = (state: RootState) => state.selections;

export const selectDestinationAvailability = createSelector(
  [selectSelectionsState],
  (selections) => selections.destination?.availability || null
);

export const selectCurrentDestination = createSelector(
  [selectSelectionsState],
  (selections) => selections.destination
);

export const selectSelectedDepartureFlight = createSelector(
  [selectSelectionsState],
  (selections) => selections.departureFlight
);

export const selectSelectedReturnFlight = createSelector(
  [selectSelectionsState],
  (selections) => selections.returnFlight
);

export const selectSelectedAccommodation = createSelector(
  [selectSelectionsState],
  (selections) => selections.accomodation
);

export const selectAllSelections = createSelector(
  [selectSelectionsState],
  (selections) => selections
);

export const selectIsTripComplete = createSelector(
  [
    selectSelectedDepartureFlight,
    selectSelectedReturnFlight,
    selectSelectedAccommodation,
    selectDestinationAvailability
  ],
  (departure, returnFlight, accommodation, availability) => {
    const accommodationRequired = availability?.hasAccommodations ?? true;
    const departureFlightsRequired = availability?.hasDepartureFlights ?? true;
    const returnFlightsRequired = availability?.hasReturnFlights ?? true;

    return (
      (!departureFlightsRequired || (!!departure && !!returnFlight)) &&
      (!returnFlightsRequired || !!returnFlight) &&
      (!accommodationRequired || !!accommodation)
    );
  }
);

export const selectStepAvailability = createSelector(
  [selectDestinationAvailability],
  (availability) => ({
    canSelectDeparture: availability?.hasDepartureFlights ?? false,
    canSelectReturn: availability?.hasReturnFlights ?? false,
    canSelectAccommodation: availability?.hasAccommodations ?? false
  })
);