import { useAppSelector } from '../../store/hooks';
import {
  selectCurrentDestination,
  selectSelectedDepartureFlight,
  selectSelectedReturnFlight,
  selectSelectedAccommodation,
  selectStepAvailability,
} from '../../store/selectionSelectors';
import { DateTime } from 'luxon';
import './Sidebar.css';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faEdit, faLocationDot, faCalendarWeek, faPeopleGroup } from '@fortawesome/free-solid-svg-icons';
import TripOverviewButton from '../searchResults/TripOverviewButton';

type SidebarProps = {
  currentStep: string;
  onStepChange: (step: string) => void;
  steps: { key: string; label: string }[];
};

const Sidebar = ({ currentStep, onStepChange, steps }: SidebarProps) => {
  const searchParams = useAppSelector((state) => state.search);
  const destination = useAppSelector(selectCurrentDestination);
  const departureFlight = useAppSelector(selectSelectedDepartureFlight);
  const returnFlight = useAppSelector(selectSelectedReturnFlight);
  const accommodation = useAppSelector(selectSelectedAccommodation);
  const stepAvailability = useAppSelector(selectStepAvailability);

  const handleStepClick = (step: string) => {
    if (
      (step === 'departure' && !stepAvailability.canSelectDeparture) ||
      (step === 'return' && !stepAvailability.canSelectReturn) ||
      (step === 'accommodation' && !stepAvailability.canSelectAccommodation)
    ) return;

    onStepChange(step);
  };

  return (
    <div className="sidebar">
      <div className="sticky-content">
        
        <div className="search-parameters-container" onClick={() => onStepChange('search')}>
          <div className="search-parameters">
            <h2>Search Parameters</h2>
            <hr />
            <p className="search-params-label"><FontAwesomeIcon icon={faLocationDot} />Departure Airport</p>
            <p>{searchParams.departureAirport?.name || 'Not selected'}</p>

            <p className="search-params-label"><FontAwesomeIcon icon={faCalendarWeek} /> Dates</p>
            <p>
              {searchParams.dateRange[0]
                ? DateTime.fromISO(searchParams.dateRange[0].toString()).toFormat('dd MMM')
                : 'Depart'} 
              - 
              {searchParams.dateRange[1]
                ? DateTime.fromISO(searchParams.dateRange[1].toString()).toFormat('dd MMM')
                : 'Return'}
            </p>

            <p className="search-params-label"><FontAwesomeIcon icon={faPeopleGroup} /> Travelers</p>
            <p>
              {searchParams.passengers.adults} Adult{searchParams.passengers.adults > 1 ? 's' : ''}
              {searchParams.passengers.children > 0 && `, ${searchParams.passengers.children} Child${searchParams.passengers.children > 1 ? 'ren' : ''}`}
              {searchParams.passengers.infants > 0 && `, ${searchParams.passengers.infants} Infant${searchParams.passengers.infants > 1 ? 's' : ''}`}
            </p>
            <p>{searchParams.passengers.rooms} Room{searchParams.passengers.rooms > 1 ? 's' : ''}</p>

            <div className="search-parameters-overlay">
              <FontAwesomeIcon icon={faEdit} className="edit-icon" />
            </div>
          </div>
        </div>

        <div className="selected-items">
          <h2>Your Selections</h2>
          <hr />
          {
            steps
            .filter(step => step.key !== 'search' && step.key !== 'overview')
            .map(({ key, label }) => {
              let completed = false;
              let disabled = false;
              let displayValue = "Not selected";

              switch (key) {
                case 'destination':
                  completed = !!destination;
                  displayValue = destination ? `${destination.city}, ${destination.country}` : displayValue;
                  break;
                case 'departure':
                  completed = !!departureFlight;
                  disabled = !stepAvailability.canSelectDeparture;
                  displayValue = departureFlight
                    ? DateTime.fromISO(departureFlight.departureTime.toString()).toFormat("dd MMM HH:mm")
                    : disabled ? "No flights available" : displayValue;
                  break;
                case 'accommodation':
                  completed = !!accommodation;
                  disabled = !stepAvailability.canSelectAccommodation;
                  displayValue = accommodation ? accommodation.name : disabled ? "No accommodations available" : displayValue;
                  break;
                case 'return':
                  completed = !!returnFlight;
                  disabled = !stepAvailability.canSelectReturn;
                  displayValue = returnFlight
                    ? DateTime.fromISO(returnFlight.departureTime.toString()).toFormat("dd MMM HH:mm")
                    : disabled ? "No flights available" : displayValue;
                  break;
                case 'search':
                  displayValue = "Modify search";
                  completed = true;
                  break;
                case 'overview':
                  displayValue = "Trip summary";
                  completed = true;
                  break;
              }

              return (
                <div
                  key={key}
                  className={`selected-item ${currentStep === key ? 'active' : ''} ${completed ? 'completed' : ''} ${disabled ? 'disabled' : ''}`}
                  onClick={() => !disabled && handleStepClick(key)}
                >
                  <p className="selected-item-label">{label}:</p>
                  <p>{displayValue}</p>
                </div>
              );
            })
          }
        </div>

        {/* Overview Button */}
        <TripOverviewButton onClick={() => handleStepClick('overview')} />
      </div>
    </div>
  );
};

export default Sidebar;
