import { useState } from 'react';

interface Props {
    airport_id: number,
    airport_name: string,
    airport_city_name: string,
    airport_country_name: string,
    airport_iata_code: string,
}

function Airport ({
    airport_id,
    airport_name,
    airport_city_name,
    airport_country_name,
    airport_iata_code,
}: Props) {
  const [isEditing, setIsEditing] = useState(false);
  const [cityName, setCityName] = useState(airport_city_name);
  const [countryName, setCountryName] = useState(airport_country_name);

  const handleEditClick = () => {
    setIsEditing(true);
  };

  const handleSaveClick = () => {
    setIsEditing(false);
    // Send updated values to the server or handle them as needed
    console.log('Updated City:', cityName);
    console.log('Updated Country:', countryName);
  };

  return (
    <div>
      <h2>{airport_id}: {airport_name} ({airport_iata_code})</h2>
      <li>
        {isEditing ? (
          <>
          <label>City:</label>
            <input 
              type="text" 
              value={cityName} 
              onChange={(e) => setCityName(e.target.value)} 
            />
            <label>Country:</label>
            <input 
              type="text" 
              value={countryName} 
              onChange={(e) => setCountryName(e.target.value)} 
            />
          </>
        ) : (
          <>
            <p>City: {cityName}</p>
            <p>Country: {countryName}</p>
          </>
        )}
      </li>
      <div>
        {isEditing ? (
          <button onClick={handleSaveClick}>Save</button>
        ) : (
          <button onClick={handleEditClick}>Edit</button>
        )}
        <button>Delete</button>
      </div>
    </div>
  )
}

export default Airport;