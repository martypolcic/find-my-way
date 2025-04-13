import { useState } from "react";
import "./SearchSection.css";
import AirportSearchButton from "./AirportSelectButton";
import DateSelectButton from "./DateSelectButton";
import TravellerSelectorButton from "./TravellersSelectButton";
import SearchButton from "./SearchButton";
import { Airport } from "../../types";

const SearchSection = () => {
  const [localSearchParams, setLocalSearchParams] = useState({
    departureAirport: null as Airport | null,
    dateRange: [null, null] as [string | null, string | null],
    passengers: {
      adults: 1,
      children: 0,
      infants: 0,
      rooms: 1
    }
  });

  const handleAirportChange = (airport: Airport | null) => {
    setLocalSearchParams(prev => ({
      ...prev,
      departureAirport: airport
    }));
  };

  const handleDateChange = (dates: [string | null, string | null]) => {
    setLocalSearchParams(prev => ({
      ...prev,
      dateRange: dates
    }));
  };

  const handlePassengersChange = (passengers: {
    adults: number;
    children: number;
    infants: number;
    rooms: number;
  }) => {
    setLocalSearchParams(prev => ({
      ...prev,
      passengers
    }));
  };

  return (
    <div className="relative flex flex-row justify-center items-center gap-4 p-4 shadow-lg rounded-xl">
      <AirportSearchButton 
        selectedAirport={localSearchParams.departureAirport} 
        onAirportChange={handleAirportChange} 
      />
      <DateSelectButton 
        selectedDates={localSearchParams.dateRange} 
        onDateChange={handleDateChange} 
      />
      <TravellerSelectorButton 
        selectedPassengers={localSearchParams.passengers} 
        onPassengersChange={handlePassengersChange} 
      />
      <SearchButton 
        searchParams={localSearchParams} 
      />
    </div>
  );
};

export default SearchSection;