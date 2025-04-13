import Card from './Card';
import { Flight } from '../../types/';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faPlane } from '@fortawesome/free-solid-svg-icons';
import './FlightCard.css';

interface FlightCardProps {
  flight: Flight;
  type: 'departure' | 'return';
  selected?: boolean;
  onSelect?: () => void;
}

export const FlightCard: React.FC<FlightCardProps> = ({
  flight,
  type,
  selected,
  onSelect
}) => {
  const departureTime = new Date(flight.departureDate).toLocaleTimeString([], { 
    hour: '2-digit', 
    minute: '2-digit',
    hour12: false 
  });
  
  const arrivalTime = new Date(flight.arrivalDate).toLocaleTimeString([], { 
    hour: '2-digit', 
    minute: '2-digit',
    hour12: false 
  });
  
  const durationHours = (new Date(flight.arrivalDate).getTime() - new Date(flight.departureDate).getTime()) / (1000 * 60 * 60);
  const durationMinutes = Math.round((durationHours % 1) * 60);
  const durationText = `${Math.floor(durationHours)}h ${durationMinutes}m`;

  return (
    <Card
      type="flight"
      title={`${flight.departureAirportIataCode} → ${flight.arrivalAirportIataCode}`}
      subtitle={`${type === 'departure' ? 'Departure' : 'Return'} Flight`}
      price={`€${flight.price_value.toFixed(2)}`}
      selected={selected}
      onSelect={onSelect}
      className="flight-card"
      details={
        <div className="flight-details">
          <div className="flight-route">
            <div className="flight-time">
              <span className="time">{departureTime}</span>
              <span className="airport">{flight.departureAirportIataCode}</span>
            </div>
            
            <div className="flight-duration">
              <FontAwesomeIcon icon={faPlane} className="plane-icon" />
              <span className="duration-text">{durationText}</span>
            </div>
            
            <div className="flight-time">
              <span className="time">{arrivalTime}</span>
              <span className="airport">{flight.arrivalAirportIataCode}</span>
            </div>
          </div>
          
          <div className="flight-date">
            {new Date(flight.departureDate).toLocaleDateString([], {
              weekday: 'short',
              day: 'numeric',
              month: 'short'
            })}
          </div>
        </div>
      }
    />
  );
};