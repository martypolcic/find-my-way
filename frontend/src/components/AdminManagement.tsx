import { useState, useEffect } from 'react';
import axios from 'axios';
import './AdminManagement.css';

interface Airport {
  id: number;
  iataCode: string;
  airportName: string;
  countryName: string;
  cityName: string;
  latitudeDeg: string;
  longitudeDeg: string;
}

function AdminManagement() {
  const [airports, setAirports] = useState<Airport[] | null>(null);
  const [editingAirportId, setEditingAirportId] = useState<number | null>(null);
  const [editedAirport, setEditedAirport] = useState<Airport | null>(null);
  const [loading, setLoading] = useState<boolean>(true);

  useEffect(() => {
    const fetchAirports = async () => {
      try {
        const response = await axios.get('/api/v1/airports');
        setAirports(response.data.data);
      } catch (error) {
        console.error('Error fetching airports:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchAirports();
  }, []);

  const handleEditClick = (airport: Airport) => {
    setEditingAirportId(airport.id);
    setEditedAirport({ ...airport });
  };

  const handleInputChange = (field: keyof Airport, value: string) => {
    if (editedAirport) {
      setEditedAirport({ ...editedAirport, [field]: value });
    }
  };

  const handleSaveClick = async () => {
    if (editedAirport) {
      const snakeCaseAirport = {
        id: editedAirport.id,
        iata_code: editedAirport.iataCode,
        airport_name: editedAirport.airportName,
        country_name: editedAirport.countryName,
        city_name: editedAirport.cityName,
        latitude_deg: editedAirport.latitudeDeg,
        longitude_deg: editedAirport.longitudeDeg
      };
  
      try {
        const response = await axios.put(`/api/v1/airports/${editedAirport.id}`, snakeCaseAirport);
  
        // Update the airport in the state
        setAirports((prevAirports) =>
          prevAirports!.map(airport =>
            airport.id === editedAirport.id ? { ...airport, ...editedAirport } : airport
          )
        );
  
        // Clear editing state
        setEditingAirportId(null);
        setEditedAirport(null);
      } catch (error) {
        console.error('Error updating airport:', error);
      }
    }
  };
  
  const handleCreateClick = async () => {
    if (editedAirport) {
      const snakeCaseAirport = {
        iata_code: editedAirport.iataCode,
        airport_name: editedAirport.airportName,
        country_name: editedAirport.countryName,
        city_name: editedAirport.cityName,
        latitude_deg: editedAirport.latitudeDeg,
        longitude_deg: editedAirport.longitudeDeg
      };
  
      console.log('snakeCaseAirport:', snakeCaseAirport);
      try {
        const response = await axios.post(`/api/v1/airports`, snakeCaseAirport);
  
        // Update the airport in the state
        setAirports((prevAirports) =>
          prevAirports!.concat({ ...editedAirport, id: response.data.data.id })
        );
  
        // Clear editing state
        setEditingAirportId(null);
        setEditedAirport(null);
      } catch (error) {
        console.error('Error creating airport:', error);
      }
    }
  }
  

  const handleDeleteClick = async (id: number) => {
    try {
      await axios.delete(`/api/v1/airports/${id}`);
      setAirports(airports!.filter(airport => airport.id !== id));
    } catch (error) {
      console.error('Error deleting airport:', error);
    }
  };

  if (loading) {
    return <div>Loading...</div>;
  }

  if (!airports || airports.length === 0) {
    return <div>No airports available</div>;
  }

  return (
    <div className="admin-management">
      <h1>Manage Airports</h1>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>IATA Code</th>
            <th>Airport Name</th>
            <th>Country Name</th>
            <th>City Name</th>
            <th>Latitude</th>
            <th>Longitude</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          {/* create new airport */}
          {
            editingAirportId === 0 ? (
              <tr>
                <td>New</td>
                <td>
                  <input 
                    type="text" 
                    value={editedAirport?.iataCode || ''} 
                    onChange={(e) => handleInputChange('iataCode', e.target.value)} 
                    required
                  />
                </td>
                <td>
                  <input 
                    type="text" 
                    value={editedAirport?.airportName || ''} 
                    onChange={(e) => handleInputChange('airportName', e.target.value)} 
                    required
                  />
                </td>
                <td>
                  <input 
                    type="text" 
                    value={editedAirport?.countryName || ''} 
                    onChange={(e) => handleInputChange('countryName', e.target.value)} 
                    required
                  />
                </td>
                <td>
                  <input 
                    type="text" 
                    value={editedAirport?.cityName || ''} 
                    onChange={(e) => handleInputChange('cityName', e.target.value)} 
                    required
                  />
                </td>
                <td>
                  <input 
                    type="text" 
                    value={editedAirport?.latitudeDeg || ''} 
                    onChange={(e) => handleInputChange('latitudeDeg', e.target.value)} 
                    required
                  />
                </td>
                <td>
                  <input 
                    type="text" 
                    value={editedAirport?.longitudeDeg || ''} 
                    onChange={(e) => handleInputChange('longitudeDeg', e.target.value)} 
                    required
                  />
                </td>
                <td>
                  <button onClick={handleCreateClick}>Create Airport</button>
                </td>
              </tr>
            ) : (
              <tr>
                <td>New</td>
                <td>
                  <button onClick={() => {
                    setEditingAirportId(0);
                    setEditedAirport({
                      id: 0,
                      iataCode: '',
                      airportName: '',
                      countryName: '',
                      cityName: '',
                      latitudeDeg: '',
                      longitudeDeg: ''
                    });
                  }
                    }>Create</button>
                </td>
              </tr>
            )
          }
          {
            airports.map((airport) => (
            <tr key={airport.id}>
              <td>{airport.id}</td>
              <td>
                {editingAirportId === airport.id ? (
                  <input 
                    type="text" 
                    value={editedAirport?.iataCode || ''} 
                    onChange={(e) => handleInputChange('iataCode', e.target.value)} 
                    required
                  />
                ) : (
                  airport.iataCode
                )}
              </td>
              <td>
                {editingAirportId === airport.id ? (
                  <input 
                    type="text" 
                    value={editedAirport?.airportName || ''} 
                    onChange={(e) => handleInputChange('airportName', e.target.value)} 
                    required
                  />
                ) : (
                  airport.airportName
                )}
              </td>
              <td>
                {editingAirportId === airport.id ? (
                  <input 
                    type="text" 
                    value={editedAirport?.countryName || ''} 
                    onChange={(e) => handleInputChange('countryName', e.target.value)} 
                    required
                  />
                ) : (
                  airport.countryName
                )}
              </td>
              <td>
                {editingAirportId === airport.id ? (
                  <input 
                    type="text" 
                    value={editedAirport?.cityName || ''} 
                    onChange={(e) => handleInputChange('cityName', e.target.value)} 
                    required
                  />
                ) : (
                  airport.cityName
                )}
              </td>
              <td>
                {editingAirportId === airport.id ? (
                  <input 
                    type="text" 
                    value={editedAirport?.latitudeDeg || ''} 
                    onChange={(e) => handleInputChange('latitudeDeg', e.target.value)} 
                    required
                  />
                ) : (
                  airport.latitudeDeg
                )}
              </td>
              <td>
                {editingAirportId === airport.id ? (
                  <input 
                    type="text" 
                    value={editedAirport?.longitudeDeg || ''} 
                    onChange={(e) => handleInputChange('longitudeDeg', e.target.value)} 
                    required
                  />
                ) : (
                  airport.longitudeDeg
                )}
              </td>
              <td>
                {editingAirportId === airport.id ? (
                  <button onClick={handleSaveClick}>Save</button>
                ) : (
                  <button onClick={() => handleEditClick(airport)}>Edit</button>
                )}
                <button onClick={() => handleDeleteClick(airport.id)}>Delete</button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

export default AdminManagement;
