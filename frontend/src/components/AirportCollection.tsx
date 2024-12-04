import { useEffect, useState } from "react";
import Airport from "./Airport";
import AirportForm from "./AirportForm";
import './AirportCollection.css';

interface Airport {
  id: number;
  airportName: string;
  cityName: string;
  countryName: string;
  iataCode: string;
}

function AirportCollection() {
  const [airports, setAirports] = useState<Airport[]>([]);

  useEffect(() => {
    fetchAirports();
  }, []);

  const fetchAirports = async () => {
    try {
      const response = await fetch("http://localhost:81/api/v1/airports");
      const { data } = await response.json();
      setAirports(data);
    } catch (error) {
      console.error("Error fetching airports:", error);
    }
  };

  const handleDelete = (id: number) => {
    setAirports(prevAirports => prevAirports.filter(airport => airport.id !== id));
  };

  const handleCreate = (newAirport: Airport) => {
    fetchAirports();
  };

  const handleEdit = (id: number, updatedData: Partial<Airport>) => {
    setAirports(prevAirports => prevAirports.map(airport => 
      airport.id === id ? { ...airport, ...updatedData } : airport
    ));
  };

  return (
    <div className="airport-collection">
      <AirportForm onCreate={handleCreate} />
      {airports.map((airport) => (
        <Airport 
          key={airport.id} 
          id={airport.id} 
          airport_name={airport.airportName}
          city_name={airport.cityName}
          country_name={airport.countryName}
          iata_code={airport.iataCode}
          onDelete={handleDelete}
          onEdit={handleEdit}
        />
      ))}
    </div>
  );
}

export default AirportCollection;