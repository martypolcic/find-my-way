import Card from './Card';
import roomImage from '../../assets/Room.jpg';
import { AccomodationWithOffers } from '../../types';
import './AccomodationCard.css';

interface AccomodationCardProps {
  accommodation: AccomodationWithOffers;
  selected?: boolean;
  onSelect?: (index : string) => void;
}

export const AccomodationCard: React.FC<AccomodationCardProps> = ({
  accommodation,
  selected,
  onSelect
}) => {
  const lowestOffer = accommodation.offers.length > 0
    ? Math.min(...accommodation.offers.map(o => parseFloat(o.price)))
    : null;

  return (
    <Card
      type="accommodation"
      title={accommodation.accomodation.name}
      subtitle={`${accommodation.accomodation.provider}`}
      price={lowestOffer ? `From €${lowestOffer.toFixed(2)}` : 'No prices available'}
      image={roomImage}
      selected={selected}
      onSelect={() => onSelect && onSelect(accommodation.accomodation.id)}
      className="accommodation-card"
      details={
        <div className="accommodation-details">
          {accommodation.offers.length > 0 ? (
            <ul className="offer-list">
              {accommodation.offers.map((offer, index) => (
                <li key={index}>
                  €{offer.price} • {new Date(offer.checkIn).toLocaleDateString()} - {new Date(offer.checkOut).toLocaleDateString()}
                </li>
              ))}
            </ul>
          ) : (
            <div className="no-offers">No current offers</div>
          )}
        </div>
      }
    />
  );
};