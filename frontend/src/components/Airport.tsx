import { useState } from 'react';
import './Airport.css';

interface Props {
  id: number,
  airport_name: string,
  city_name: string,
  country_name: string,
  iata_code: string,
  onDelete: (id: number) => void,
  onEdit: (id: number, updatedData: any) => void,
}

const Airport: React.FC<Props> = ({
    id,
    airport_name,
    city_name,
    country_name,
    iata_code,
    onDelete,
    onEdit,
}) => {
  const [isEditing, setIsEditing] = useState(false);
  const [airportData, setAirportData] = useState({
    airport_name,
    iata_code: iata_code,
    city_name: city_name,
    country_name: country_name,
  });

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setAirportData(prevData => ({
      ...prevData,
      [name]: value,
    }));
  };

  const handleEditClick = () => setIsEditing(true);

  const handleDeleteClick = () => {
    fetch(`http://localhost:81/api/v1/airports/${id}`, {
      method: 'DELETE',
    })
    .then(() => {
      onDelete(id); // Call the onDelete callback
    })
    .catch(error => {
      console.error('Error deleting airport:', error);
    });
  };

  const handleSaveClick = () => {
    fetch(`http://localhost:81/api/v1/airports/${id}`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'accept': 'application/json',
      },
      body: JSON.stringify(airportData),
    })
    .then(response => response.json())
    .then(updatedData => {
      setIsEditing(false);
      onEdit(id, updatedData);
    })
    .catch(error => {
      console.error('Error updating airport data:', error);
      setIsEditing(false);
    });
};

  return (
    <div className={isEditing ? 'airport-form' : 'airport-row'}>
      {isEditing ? (
        <>
          <h2>ID: {id}</h2>
          {['airport_name', 'iata_code', 'city_name', 'country_name'].map(field => (
            <div key={field}>
              <label>{field.replace('_', ' ').toUpperCase()}:</label>
              <input 
                type="text" 
                name={field}
                value={airportData[field as keyof typeof airportData]}
                onChange={handleChange}
                pattern='[A-Za-z0-9\s]+'
              />
            </div>
          ))}
        </>
      ) : (
        <>
          <h2>{id}. {airportData.airport_name} ({airportData.iata_code})</h2>
          <p>City: {airportData.city_name}</p>
          <p>Country: {airportData.country_name}</p>
        </>
      )}
      <div className='airport-button-wrapper'>
        {isEditing ? (
          <button onClick={handleSaveClick}>Save</button>
        ) : (
          <button onClick={handleEditClick}>Edit</button>
        )}
        <button onClick={handleDeleteClick}>Delete</button>
      </div>
    </div>
  );
};

export default Airport;