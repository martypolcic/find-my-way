import Card from './Card';
import { DestinationResults } from '../../types';
import destinationImage from '../../assets/destination.jpg';
import './DestinationCard.css'

interface DestinationCardProps {
  country: string;
  destination: string;
  data: DestinationResults;
  selected?: boolean;
  onSelect?: () => void;
}

export const DestinationCard: React.FC<DestinationCardProps> = ({
  country,
  destination,
  data,
  selected,
  onSelect
}) => {
  return (
    <Card
      type="destination"
      title={destination}
      subtitle={country}
      image={destinationImage}
      selected={selected}
      onSelect={onSelect}
      className="destination-card"
      details={
        <div className="destination-details">
          <span>{data.departureFlights?.length} departure flights, </span>
          <span>{data.returnFlights?.length} return flights, </span>
          <span>{data.accomodations ? data.accomodations.length : 'No '} accommodations</span>
        </div>
      }
    />
  );
};