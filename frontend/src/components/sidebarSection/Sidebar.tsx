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
import { faLocationDot, faCalendarWeek, faPeopleGroup, faPlaneDeparture, faPlaneArrival, faKey, faUmbrellaBeach, faEdit } from '@fortawesome/free-solid-svg-icons';
import TripOverviewButton from '../searchResults/TripOverviewButton';

const Sidebar = ({currentStep, onStepChange} : {currentStep: string, onStepChange:(step: string) => void}) => {
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
      (step === 'accomodation' && !stepAvailability.canSelectAccommodation)
    ) {
      return;
    }
    onStepChange(step);
  };

  return (
    <div className="sidebar">
      <div className='sticky-content'>

        {/* Search Params */}
        <div 
          className="search-parameters-container" 
          onClick={() => onStepChange('search')}
        >
          <div className="search-parameters" >
            <h2>Search Parameters</h2>
            <hr />

            <p className='search-params-label'>
              <FontAwesomeIcon icon={faLocationDot} />
              Departure Airport
            </p>
            <p> 
              {
                searchParams.departureAirport ? searchParams.departureAirport.name : "Not selected"
              }
            </p>

            <p className='search-params-label'>
              <FontAwesomeIcon icon={faCalendarWeek} />
              Dates
            </p>
            <p>
              {
                searchParams.dateRange[0] ? DateTime.fromISO(searchParams.dateRange[0].toString()).toFormat("dd MMM") : "Depart"
              } 
              -
              {
                searchParams.dateRange[1] ? DateTime.fromISO(searchParams.dateRange[1].toString()).toFormat("dd MMM") : "Return"
              }
            </p>

            <p className='search-params-label'>
              <FontAwesomeIcon icon={faPeopleGroup} />
              Traveller{searchParams.passengers.adults + searchParams.passengers.children + searchParams.passengers.infants > 1 ? 's' : ''}
            </p>
            {
              searchParams.passengers.adults > 0 && (
                <p>
                  {searchParams.passengers.adults} Adult{searchParams.passengers.adults > 1 ? 's' : ''}
                </p>
              )
            }
            {
              searchParams.passengers.children > 0 && (
                <p>
                  {searchParams.passengers.children} Child{searchParams.passengers.children > 1 ? 'ren' : ''}
                </p>
              )
            }
            {
              searchParams.passengers.infants > 0 && (
                <p>
                  {searchParams.passengers.infants} Infant{searchParams.passengers.infants > 1 ? 's' : ''}
                </p>
              )
            }
            {
              searchParams.passengers.rooms > 0 && (
                <p>
                  {searchParams.passengers.rooms} Room{searchParams.passengers.rooms > 1 ? 's' : ''}
                </p>
              )
            }
            <div className="search-parameters-overlay">
              <FontAwesomeIcon icon={faEdit} className="edit-icon" />
            </div>
          </div>
        </div>

        {/* Use choices */}
        <div className="selected-items">
          <h2>Your choices</h2>
          <hr />

          <div 
            className={`selected-item ${currentStep === 'destination' ? 'active' : ''} ${destination ? 'completed' : ''}`}
            onClick={() => onStepChange('destination')}
          >
            <p className='selected-item-label'>
              <FontAwesomeIcon icon={faUmbrellaBeach} className='mr-2' />
              Destination:
            </p>
            <p>
              {
                destination 
                ? `${destination.city}, ${destination.country}`
                : "Not selected"
              }
            </p>
          </div>

          <div 
            className={`selected-item ${currentStep === 'departure' ? 'active' : ''} ${departureFlight ? 'completed' : ''} ${!stepAvailability.canSelectDeparture ? 'disabled' : ''}`}
            onClick={() => handleStepClick('departure')}
          >
            <p className='selected-item-label'>
              <FontAwesomeIcon icon={faPlaneDeparture} className='mr-2'/>
              Departure:
            </p>
            <p>
              {
                !stepAvailability.canSelectDeparture && (
                  <span className="unavailable-text">No flights available</span>
                )
              }
              {
                departureFlight
                ? <span>{DateTime.fromISO(departureFlight.departureTime.toString()).toFormat("dd MMM HH:mm")}</span> 
                : stepAvailability.canSelectDeparture 
                  ? "Not selected"
                  : ''
              }
            </p>
          </div>

          <div
            className={`selected-item ${currentStep === 'accomodation' ? 'active' : ''} ${accommodation ? 'completed' : ''} ${!stepAvailability.canSelectAccommodation ? 'disabled' : ''}`}
            onClick={() => handleStepClick('accomodation')}
          >
            <p className='selected-item-label'>
              <FontAwesomeIcon icon={faKey} className='mr-2'/>
              Accommodation:
            </p>
            <p>
              {
                !stepAvailability.canSelectAccommodation && (
                  <span className="unavailable-text">No accommodations available</span>
                )
              }
              {
                accommodation
                ? `${accommodation?.name}`
                : stepAvailability.canSelectAccommodation 
                  ? "Not selected"
                  : ''
              }
            </p>
          </div>

          <div 
            className={`selected-item ${currentStep === 'return' ? 'active' : ''} ${returnFlight ? 'completed' : ''} ${!stepAvailability.canSelectReturn ? 'disabled' : ''}`}
            onClick={() => handleStepClick('return')}
          >
            <p className='selected-item-label'>
              <FontAwesomeIcon icon={faPlaneArrival} className='mr-2'/>
              Return:
            </p>
            <p>
              {
                !stepAvailability.canSelectReturn && (
                  <span className="unavailable-text">No flights available</span>
                )
              }
              {
                returnFlight
                ? <span>{DateTime.fromISO(returnFlight.departureTime.toString()).toFormat("dd MMM HH:mm")}</span> 
                : stepAvailability.canSelectReturn
                  ? "Not selected"
                  : ''
              }
            </p>
          </div>
        </div>

        {/* Buttons */}
        <TripOverviewButton onClick={() => handleStepClick('overview')} />
      </div>
      
    </div>
  );
};

export default Sidebar;