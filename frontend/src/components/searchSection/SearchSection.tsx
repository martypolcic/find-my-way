import { useState, useEffect } from "react";
import "./SearchSection.css";
import AirportSearchButton from "./AirportSelectButton";
import DateSelectButton from "./DateSelectButton";
import TravellerSelectorButton from "./TravellersSelectButton";
import SearchButton from "./SearchButton";
import { Airport } from "../../types";
import { useAppSelector } from "../../store/hooks";

interface SearchSectionProps {
  variant?: 'default' | 'overlay';
  onSearchComplete?: () => void;
}

const SearchSection = ({ variant = 'default', onSearchComplete } : SearchSectionProps ) => {
  const initialSearchParams = useAppSelector((state) => state.search);
  const [departureAirport, setDepartureAirport] = useState<Airport | null>(null);
  const [dateRange, setDateRange] = useState<[string | null, string | null]>([null, null]);
  const [passengers, setPassengers] = useState({
    adults: 1,
    children: 0,
    infants: 0,
    rooms: 1
  });

  useEffect(() => {
    if (initialSearchParams) {
      setDepartureAirport(initialSearchParams.departureAirport);
      setDateRange(initialSearchParams.dateRange);
      setPassengers(initialSearchParams.passengers);
    }
  }, [initialSearchParams]);

  const handleAirportChange = (airport: Airport | null) => {
    setDepartureAirport(airport);
  };

  const handleDateChange = (dates: [string | null, string | null]) => {
    setDateRange(dates);
  };

  const handlePassengersChange = (passengers: {
    adults: number;
    children: number;
    infants: number;
    rooms: number;
  }) => {
    setPassengers(passengers);
  };

  return (
    <div className={`search-section-container ${variant === 'overlay' ? 'overlay-style' : 'default-style'}`}>
      <AirportSearchButton 
        selectedAirport={departureAirport} 
        onAirportChange={handleAirportChange} 
      />
      <DateSelectButton 
        selectedDates={dateRange} 
        onDateChange={handleDateChange} 
      />
      <TravellerSelectorButton 
        selectedPassengers={passengers} 
        onPassengersChange={handlePassengersChange} 
      />
      <SearchButton 
        searchParams={{
          departureAirport,
          dateRange,
          passengers
        }}
        onSearchComplete={onSearchComplete}
      />
    </div>
  );
};

export default SearchSection;