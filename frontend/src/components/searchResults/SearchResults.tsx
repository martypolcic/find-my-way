import { AccomodationWithOffers, Flight } from '../../types';
import { useAppDispatch, useAppSelector } from '../../store/hooks';
import { 
  selectNormalizedResults,
  selectRequestState 
} from '../../store/searchSelectors';
import {
  selectDestination,
  selectDepartureFlight,
  selectReturnFlight,
  selectAccommodation,
} from '../../store/selectionSlice';
import {
  selectCurrentDestination,
  selectSelectedDepartureFlight,
  selectSelectedReturnFlight,
  selectSelectedAccommodation,
} from '../../store/selectionSelectors';
import { DestinationCard } from '../cards/DestinationCard';
import { FlightCard } from '../cards/FlightCard';
import { AccomodationCard } from '../cards/AccomodationCard';
import './SearchResults.css';
import LoadingComponent from '../loadingComponent/LoadingComponent';
import TripOverview from './TripOverview';

const SearchResults = ({ currentStep, onStepChange}: { currentStep: string, onStepChange:(step: string) => void }) => {
  const dispatch = useAppDispatch();
  const results = useAppSelector(selectNormalizedResults);
  const status = useAppSelector(selectRequestState).status;
  
  // Get current selections
  const currentDestination = useAppSelector(selectCurrentDestination);
  const selectedDeparture = useAppSelector(selectSelectedDepartureFlight);
  const selectedReturn = useAppSelector(selectSelectedReturnFlight);
  const selectedAccommodation = useAppSelector(selectSelectedAccommodation);

  // Find the selected destination results
  const selectedResult = currentDestination 
    ? results?.find(r => 
        r.country === currentDestination.country && 
        r.destination === currentDestination.city
      )
    : null;

  const handleSelectDestination = (country: string, destination: string, data: {
    departureFlights: Flight[];
    returnFlights: Flight[];
    accommodations: AccomodationWithOffers[];
  }) => {
    dispatch(selectDestination({ 
      country, 
      city: destination,
      departureFlights: data.departureFlights,
      returnFlights: data.returnFlights,
      accommodations: data.accommodations
    }));
    if (data.departureFlights.length > 0) {
      onStepChange('departure');
    } else if (data.returnFlights.length > 0) {
      onStepChange('return');
    } else if (data.accommodations.length > 0) {
      onStepChange('accomodation');
    }
  };

  const handleSelectFlight = (flight: Flight, type: 'departure' | 'return') => {
    if (type === 'departure') {
      dispatch(selectDepartureFlight(flight));
    } else {
      dispatch(selectReturnFlight(flight));
    }
  };

  const handleSelectAccommodation = (acc: AccomodationWithOffers) => {
    dispatch(selectAccommodation({ accommodation: acc }));
  };

  if (status === 'loading') return <LoadingComponent />;
  if (status === 'failed') return <div className="error-message">Error loading results</div>;
  if (!results || results.length === 0) return <div className="no-results">No matching trips found</div>;

  return (
    <div className="search-results-container">
      { 
        /* Destination Selection */
        currentStep === 'destination' &&
        <section className="results-section">
          <h2>Available destinatios</h2>
          <div className="cards-grid">
            {
              results.map(({ country, destination, departureFlights, accomodations, returnFlights }) => (
                <DestinationCard
                  key={`${country}-${destination}`}
                  country={country}
                  destination={destination}
                  data={{ departureFlights, accomodations, returnFlights }}
                  selected={currentDestination?.city === destination}
                  onSelect={() => handleSelectDestination(
                    country,
                    destination,
                    {
                      departureFlights,
                      returnFlights,
                      accommodations: accomodations
                    }
                  )}
                />
              ))
            }
          </div>
        </section>
      }

      {
        /* Departure Flights (only shown when destination is selected) */
        currentStep === 'departure' &&
        selectedResult && (
          <section className="results-section">
            <h2>Departure Flights</h2>
            <div className="cards-grid">
              {selectedResult.departureFlights.map(flight => (
                <FlightCard
                  key={flight?.flightKey}
                  flight={flight}
                  type="departure"
                  selected={selectedDeparture?.id === flight?.id}
                  onSelect={() => handleSelectFlight(flight, 'departure')}
                />
              ))}
            </div>
          </section>
        )
      }

      {
        /* Accommodations (only shown when destination is selected) */
        currentStep === 'accomodation' &&
        selectedResult && (
          <section className="results-section">
            <h2>Available Accommodations</h2>
            <div className="cards-grid">
              {selectedResult.accomodations?.map((acc) => (
                <AccomodationCard
                  key={acc.accomodation.id}
                  accommodation={acc}
                  selected={selectedAccommodation?.id === acc.accomodation.id}
                  onSelect={() => handleSelectAccommodation(acc)}
                />
              ))}
            </div>
          </section>
        )
      }

      {
        /* Return Flights (only shown when destination is selected) */
        currentStep === 'return' &&
        selectedResult && 
        (
          <section className="results-section">
            <h2>Return Flights</h2>
            <div className="cards-grid">
              {selectedResult.returnFlights.map(flight => (
                <FlightCard
                  key={flight.flightKey}
                  flight={flight}
                  type="return"
                  selected={selectedReturn?.id === flight.id}
                  onSelect={() => handleSelectFlight(flight, 'return')}
                />
              ))}
            </div>
          </section>
        )
      }

      {
        currentStep === 'overview' && (
          <section className="results-section">
            <TripOverview />
          </section>
        )
      }
    </div>
  );
};

export default SearchResults;