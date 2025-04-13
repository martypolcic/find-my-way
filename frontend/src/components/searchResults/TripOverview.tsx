import { useAppSelector } from '../../store/hooks';
import {
  selectCurrentDestination,
  selectSelectedDepartureFlight,
  selectSelectedReturnFlight,
  selectSelectedAccommodation
} from '../../store/selectionSelectors';
import { DateTime } from 'luxon';
import './TripOverview.css';

const TripOverview = () => {
  const destination = useAppSelector(selectCurrentDestination);
  const departureFlight = useAppSelector(selectSelectedDepartureFlight);
  const returnFlight = useAppSelector(selectSelectedReturnFlight);
  const accommodation = useAppSelector(selectSelectedAccommodation);

  // Calculate total price
  const totalPrice = (
    (departureFlight?.price || 0) + 
    (returnFlight?.price || 0) + 
    (accommodation?.price || 0)
  ).toFixed(2);

  return (
    <div className="trip-overview">
      {/* === Destination Header === */}
      <div className="destination-header">
        <h2>{destination?.city}, {destination?.country}</h2>
        <div className="travel-dates">
          {departureFlight && (
            <span>
              {DateTime.fromISO(departureFlight.departureTime).toFormat('MMM d')}
            </span>
          )}
          {returnFlight && (
            <>
              <span> → </span>
              <span>
                {DateTime.fromISO(returnFlight.departureTime).toFormat('MMM d, yyyy')}
              </span>
            </>
          )}
        </div>
      </div>

      {/* === Three Column Layout === */}
      <div className="trip-details-grid">
        {/* Departure Flight Column */}
        <div className="trip-column">
          <h3>Departure Flight</h3>
          {departureFlight ? (
            <div className="detail-card">
              <div className="route">
                {departureFlight.departure} → {departureFlight.arrival}
              </div>
              <div className="time">
                {DateTime.fromISO(departureFlight.departureTime).toFormat('EEE, MMM d - h:mm a')}
              </div>
              <div className="price">€{departureFlight.price.toFixed(2)}</div>
            </div>
          ) : (
            <div className="empty-state">No flight selected</div>
          )}
        </div>

        {/* Accommodation Column */}
        <div className="trip-column">
          <h3>Accommodation</h3>
          {accommodation ? (
            <div className="detail-card">
              <div className="name">{accommodation.name}</div>
              <div className="dates">
                {DateTime.fromISO(accommodation.checkIn).toFormat('MMM d')} - 
                {DateTime.fromISO(accommodation.checkOut).toFormat('MMM d')}
              </div>
              <div className="price">€{accommodation.price.toFixed(2)}</div>
            </div>
          ) : (
            <div className="empty-state">No accommodation selected</div>
          )}
        </div>

        {/* Return Flight Column */}
        <div className="trip-column">
          <h3>Return Flight</h3>
          {returnFlight ? (
            <div className="detail-card">
              <div className="route">
                {returnFlight.departure} → {returnFlight.arrival}
              </div>
              <div className="time">
                {DateTime.fromISO(returnFlight.departureTime).toFormat('EEE, MMM d - h:mm a')}
              </div>
              <div className="price">€{returnFlight.price.toFixed(2)}</div>
            </div>
          ) : (
            <div className="empty-state">No flight selected</div>
          )}
        </div>
      </div>

      {/* === Price Divider === */}
      <div className="price-divider">
        <div className="divider-line"></div>
        <div className="total-price">Total: €{totalPrice}</div>
        <div className="divider-line"></div>
      </div>
    </div>
  );
};

export default TripOverview;