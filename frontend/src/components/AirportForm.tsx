import React, { useState } from 'react';
import './AirportForm.css';

interface Airport {
  airport_name: string;
  city_name: string;
  country_name: string;
  iata_code: string;
}

interface Props {
  onCreate: (newAirport: any) => void;
}

const AirportForm: React.FC<Props> = ({ onCreate }) => {
  const [airportData, setAirportData] = useState<Airport>({
    airport_name: '',
    city_name: '',
    country_name: '',
    iata_code: '',
  });

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setAirportData(prevData => ({
      ...prevData,
      [name]: value,
    }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    console.log('Creating airport:', airportData);
    try {
      const response = await fetch('http://localhost:81/api/v1/airports', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(airportData),
      });
      const newAirport = await response.json();
      onCreate(newAirport);
      setAirportData({
        airport_name: '',
        city_name: '',
        country_name: '',
        iata_code: '',
      });
    } catch (error) {
      console.error('Error creating airport:', error);
    }
  };

  return (
    <form onSubmit={handleSubmit} className='airport-create-form'>
      {Object.entries(airportData).map(([key, value]) => (
        <div key={key}>
          <label>{key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}:</label>
          <input 
            type="text" 
            name={key} 
            value={value} 
            onChange={handleChange} 
            pattern='[A-Za-z0-9\s]+'
          />
        </div>
      ))}
      <button type="submit">Create Airport</button>
    </form>
  );
};

export default AirportForm;