import { DestinationCard } from '../cards/DestinationCard';
import { FlightCard } from '../cards/FlightCard';
import { AccomodationCard } from '../cards/AccomodationCard';
import TripOverview from './TripOverview';
import StepPanel from './StepPanel';
import LoadingComponent from '../loadingComponent/LoadingComponent';
import { useAppSelector, useAppDispatch } from '../../store/hooks';
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
import { AccomodationWithOffers, Flight } from '../../types';
import SearchSection from '../searchSection/SearchSection';
import { flows } from './searchFlowConfig';
import { useRef, useEffect } from 'react';
import './SearchResults.css';

type SearchResultsProps = {
  currentStep: string;
  onStepChange: (step: string) => void;
  searchMode: 'trip' | 'flights' | 'accommodations' ;
};

const SearchResults = ({ currentStep, onStepChange, searchMode }: SearchResultsProps) => {
  const dispatch = useAppDispatch();
  const results = useAppSelector(selectNormalizedResults);
  const { status } = useAppSelector(selectRequestState);
  const setpConfig = flows[searchMode];
  const steps = setpConfig.map(step => step.key);
  const lastSelectionChanged = useRef<null | 'destination' | 'departure' | 'return' | 'accommodation'>(null);

  const currentDestination = useAppSelector(selectCurrentDestination);
  const selectedDeparture = useAppSelector(selectSelectedDepartureFlight);
  const selectedReturn = useAppSelector(selectSelectedReturnFlight);
  const selectedAccommodation = useAppSelector(selectSelectedAccommodation);

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
    lastSelectionChanged.current = 'destination';
  };

  const handleSelectFlight = (flight: Flight, type: 'departure' | 'return') => {
    if (type === 'departure') {
      dispatch(selectDepartureFlight(flight));
      lastSelectionChanged.current = 'departure';
    } else {
      dispatch(selectReturnFlight(flight));
      lastSelectionChanged.current = 'return';
    }
  };

  const handleSelectAccommodation = (acc: AccomodationWithOffers) => {
    dispatch(selectAccommodation({ accommodation: acc }));
    lastSelectionChanged.current = 'accommodation';
  };

  const handleSearchComplete = () => {
    lastSelectionChanged.current = null;
    onStepChange('destination');
  }

  useEffect(() => {
    if (!lastSelectionChanged.current) return;
  
    goToNextStep();
    lastSelectionChanged.current = null;
  }, [currentDestination, selectedDeparture, selectedReturn, selectedAccommodation]);
  

  const goToNextStep = () => {
    const currentSelections = {
      destination: !!currentDestination,
      departure: !!selectedDeparture,
      accommodation: !!selectedAccommodation,
      return: !!selectedReturn,
    };


    const availability = currentDestination?.availability;
  
    const remainingSteps = steps.filter((step) => {
      if (step === 'destination') return !currentSelections.destination;
      if (step === 'departure') return availability?.hasDepartureFlights && !currentSelections.departure;
      if (step === 'accommodation') return availability?.hasAccommodations && !currentSelections.accommodation;
      if (step === 'return') return availability?.hasReturnFlights && !currentSelections.return;
      return false;
    });

    if (remainingSteps.length > 0) {
      onStepChange(remainingSteps[0]);
    } else {
      onStepChange('overview');
    }
  };
  

  if (status === 'loading') return <LoadingComponent />;

  if (!results || results.length === 0) {
    return (
      <div className="search-results-container">
        <StepPanel stepKey="search" isActive={currentStep === 'search'}>
          <SearchSection onSearchComplete={handleSearchComplete} />
        </StepPanel>
  
        {currentStep !== 'search' && (
          <div className="no-results">
            No matching trips found
            <p className="tip-text">
              Try changing your dates or departure airport.
            </p>
          </div>
        
        )}
      </div>
    );
  }

  return (
    <div className="search-results-container">
      <StepPanel stepKey="search" isActive={currentStep === 'search'}>
        <SearchSection onSearchComplete={handleSearchComplete}/>
      </StepPanel>

      <StepPanel stepKey="destination" isActive={currentStep === 'destination'}>
        <h2>Available Destinations</h2>
        <div className="cards-grid">
          {results.map(({ country, destination, departureFlights, accomodations, returnFlights }) => (
            <DestinationCard
              key={`${country}-${destination}`}
              country={country}
              destination={destination}
              data={{ departureFlights, returnFlights, accomodations: accomodations }}
              selected={currentDestination?.city === destination}
              onSelect={() => handleSelectDestination(country, destination, {
                departureFlights,
                returnFlights,
                accommodations: accomodations
              })}
            />
          ))}
        </div>
      </StepPanel>

      <StepPanel stepKey="departure" isActive={currentStep === 'departure'}>
        <h2>Departure Flights</h2>
        <div className="cards-grid">
          {selectedResult?.departureFlights.map(flight => (
            <FlightCard
              key={flight.flightKey}
              flight={flight}
              type="departure"
              selected={selectedDeparture?.id === flight.id}
              onSelect={() => handleSelectFlight(flight, 'departure')}
            />
          ))}
        </div>
      </StepPanel>

      <StepPanel stepKey="accommodation" isActive={currentStep === 'accommodation'}>
        <h2>Available Accommodations</h2>
        <div className="cards-grid">
          {selectedResult?.accomodations?.map(acc => (
            <AccomodationCard
              key={acc.accomodation.id}
              accommodation={acc}
              selected={selectedAccommodation?.id === acc.accomodation.id}
              onSelect={() => handleSelectAccommodation(acc)}
            />
          ))}
        </div>
      </StepPanel>

      <StepPanel stepKey="return" isActive={currentStep === 'return'}>
        <h2>Return Flights</h2>
        <div className="cards-grid">
          {selectedResult?.returnFlights.map(flight => (
            <FlightCard
              key={flight.flightKey}
              flight={flight}
              type="return"
              selected={selectedReturn?.id === flight.id}
              onSelect={() => handleSelectFlight(flight, 'return')}
            />
          ))}
        </div>
      </StepPanel>

      <StepPanel stepKey="overview" isActive={currentStep === 'overview'}>
        <TripOverview />
      </StepPanel>
    </div>
  );
};

export default SearchResults;
