import { useEffect, useState } from "react";
import Airport from "./Airport";

function AirportCollection() {
  const [airports, setAirports] = useState<{
    id: number;
    airportName: string;
    cityName: string;
    countryName: string;
    iataCode: string;
  }[]>([]);

  useEffect(() => {
    fetchAirports();
  }, []);

  const fetchAirports = async () => {
    const response = await fetch("http://localhost:81/api/v1/airports");
    const {data} = await response.json();
    setAirports(data);
  };

  return (
    <div>
      <h1>Airports</h1>
      <ul>
        {airports.map((airport) => (
          <Airport 
          key={airport.id} 
          airport_id={airport.id} 
          airport_name={airport.airportName}
          airport_city_name={airport.cityName}
          airport_country_name={airport.countryName}
          airport_iata_code={airport.iataCode}
          />
        ))}
      </ul>
    </div>
  );
}

export default AirportCollection;