import { Airport } from "./SearchFlightsForm";
import "./CountryCard.css";

interface CountryCardProps {
  country: string;
  airports: Airport[];
  isExpanded: boolean;
  onToggle: () => void;
  onAirportClick: (airport: Airport) => void;
}

function CountryCard({ country, airports, isExpanded, onToggle, onAirportClick }: CountryCardProps) {
  const airportsToShow = isExpanded ? airports : airports.slice(0, 4);
  const hasMoreAirports = airports.length > 4;

  return (
    <div className="country-card">
      <h2 className="country-name">{country}</h2>
      <div className="airport-buttons">
        {airportsToShow.map((airport) => (
          <button 
            key={airport.id}
            onClick={() => onAirportClick(airport)}
            className="airport"
          >
            {airport.cityName} - {airport.airportName}
          </button>
        ))}
      </div>
      {hasMoreAirports && (
        <button 
          onClick={onToggle}
          className="show-more"
        >
          {isExpanded ? "Show Less" : "Show More"}
        </button>
      )}
    </div>
  );
}

export default CountryCard;
