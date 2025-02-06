import { useEffect, useState } from "react";
import axios from "axios";
import "./FlightManagement.css";

interface Flight {
    id: number;
    flightNumber: string;
    flightKey: string;
    departureAirportIataCode: string;
    arrivalAirportIataCode: string;
    departureDate: string;
    arrivalDate: string;
}

interface FlightApiData {
    id: number;
    flightNumber: string;
    flightKey: string;
    departureDate: string;
    arrivalDate: string;
    departureAirport: {
        id: number;
        iata_code: string;
        airport_name: string;
        country_name: string;
        city_name: string;
        latitude_deg: string;
        longitude_deg: string;
    };
    arrivalAirport: {
        id: number;
        iata_code: string;
        airport_name: string;
        country_name: string;
        city_name: string;
        latitude_deg: string;
        longitude_deg: string;
    };
    flightPrices: {
        id: number;
        price_value: string;
        currency_code: string;
        flight_id: number;
    }[];
}

function FlightManagement() {
    const [message, setMessage] = useState<string>("");
    const [flights, setFlights] = useState<Flight[] | null>([]);
    const [editingFlightId, setEditingFlightId] = useState<number | null>(null);
    const [editedFlight, setEditedFlight] = useState<Flight | null>(null);
    const [loading, setLoading] = useState<boolean>(true);

    useEffect(() => {
    fetchFlights();
    }, []);

    async function fetchFlights() {
        try {
            setLoading(true);
            const response = await axios.get("/api/v1/flights");
            
            // I receive the data and I have object departure_airport which has iata_code and I need to get it and store it in departureAirportIataCode
            const flightsData: FlightApiData[] = response.data.data;
            const flights: Flight[] = flightsData.map((flight) => {
                return {
                    id: flight.id,
                    flightNumber: flight.flightNumber,
                    flightKey: flight.flightKey,
                    departureAirportIataCode: flight.departureAirport.iata_code,
                    arrivalAirportIataCode: flight.arrivalAirport.iata_code,
                    departureDate: flight.departureDate,
                    arrivalDate: flight.arrivalDate
                };
            });
            setFlights(flights);
            
        } catch (error) {
            console.error("Error fetching flights:", error);
        } finally {
            setLoading(false);
        }
    }

    const isFlightValid = (flight: Flight) => {
        return flight.flightNumber && flight.flightKey && flight.departureAirportIataCode && flight.arrivalAirportIataCode && flight.departureDate && flight.arrivalDate;
    }

    const handleCreateClick = async () => {
        if (!editedFlight || !isFlightValid(editedFlight)) {
            setMessage("Flight is not valid");
            return;
        }
    
        const snakeCaseFlight = {
        flight_number: editedFlight.flightNumber,
        flight_key: editedFlight.flightKey,
        departure_airport_iata_code: editedFlight.departureAirportIataCode,
        arrival_airport_iata_code: editedFlight.arrivalAirportIataCode,
        departure_date: editedFlight.departureDate,
        arrival_date: editedFlight.arrivalDate
        };
    
        try {
            console.log('snakeCaseFlight:', snakeCaseFlight);
        const response = await axios.post(`/api/v1/flights`, snakeCaseFlight);
    
        // Update the flight in the state
        fetchFlights();
        setMessage("Flight created successfully");
    
        // Clear editing state
        setEditingFlightId(null);
        setEditedFlight(null);
        } catch (error) {
        console.error('Error creating airport:', error);
        }
    };

      const handleSaveClick = async () => {
        if (!editedFlight || !isFlightValid(editedFlight)) {
            setMessage("Flight is not valid");
            return;
        }

        const snakeCaseAirport = {
        id: editedFlight.id,
        flight_number: editedFlight.flightNumber,
        flight_key: editedFlight.flightKey,
        departure_airport_iata_code: editedFlight.departureAirportIataCode,
        arrival_airport_iata_code: editedFlight.arrivalAirportIataCode,
        departure_date: editedFlight.departureDate,
        arrival_date: editedFlight.arrivalDate
        };
    
        try {
        const response = await axios.put(`/api/v1/flights/${editedFlight.id}`, snakeCaseAirport);
        console.log('snakeCaseAirport:', snakeCaseAirport);
    
        // Update the flight in the state
        fetchFlights();
        setMessage("Flight updated successfully");
    
        // Clear editing state
        setEditingFlightId(null);
        setEditedFlight(null);
        } catch (error) {
        console.error('Error updating airport:', error);
        }
    };

    async function handleDeleteClick(id: number) {
        try {
            await axios.delete(`/api/v1/flights/${id}`);
            fetchFlights();
            setMessage("Flight deleted successfully");
        } catch (error) {
            console.error("Error deleting flight:", error);
        }
    }

    const handleEditClick = (flight: Flight) => {
        setEditingFlightId(flight.id);
        setEditedFlight({ ...flight });
    }

    if (loading) {
        return <p>Loading...</p>;
    }

    if (!flights || flights.length === 0) {
        return <p>No flights available</p>;
    }

    return (
        <div className="flight-management">
            <h1>Manage Flights</h1>
            {
                message && <p>{message}</p>
            }
            <table>
                <thead>
                    <tr>
                        <th>Flight Number</th>
                        <th>Flight Key</th>
                        <th>Departure Airport</th>
                        <th>Arrival Airport</th>
                        <th>Departure Time</th>
                        <th>Arrival Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {/* create new flight */}
                    {
                        editingFlightId === 0 && editedFlight ? (
                            <tr>
                                <td><input value={editedFlight.flightNumber} onChange={(e) => setEditedFlight({...editedFlight, flightNumber: e.target.value})} required /></td>
                                <td><input value={editedFlight.flightKey} onChange={(e) => setEditedFlight({...editedFlight, flightKey: e.target.value})} required /></td>
                                <td><input value={editedFlight.departureAirportIataCode} onChange={(e) => setEditedFlight({...editedFlight, departureAirportIataCode: e.target.value})} required /></td>
                                <td><input value={editedFlight.arrivalAirportIataCode} onChange={(e) => setEditedFlight({...editedFlight, arrivalAirportIataCode: e.target.value})} required /></td>
                                <td><input type="datetime-local" value={editedFlight.departureDate} onChange={(e) => setEditedFlight({...editedFlight, departureDate: e.target.value})} required /></td>
                                <td><input type="datetime-local" value={editedFlight.arrivalDate} onChange={(e) => setEditedFlight({...editedFlight, arrivalDate: e.target.value})} required /></td>
                                <td><button onClick={handleCreateClick}>Create</button></td>
                            </tr>
                        ) : (
                            <tr>
                                <td>New</td>
                                <td>
                                <button onClick={() => {
                                    setEditedFlight({
                                    id: 0,
                                    flightNumber: "",
                                    flightKey: "",
                                    departureAirportIataCode: "",
                                    arrivalAirportIataCode: "",
                                    departureDate: "",
                                    arrivalDate: "",
                                    });
                                    setEditingFlightId(0);
                                }
                                    }>Create</button>
                                </td>
                            </tr>
                        )
                    }
                    {
                        flights.map((flight) => (
                        <tr key={flight.id}>
                            {
                                editingFlightId === flight.id && editedFlight ? (
                                    <>
                                    <td><input value={editedFlight.flightNumber} onChange={(e) => setEditedFlight({...editedFlight, flightNumber: e.target.value})} required /></td>
                                    <td><input value={editedFlight.flightKey} onChange={(e) => setEditedFlight({...editedFlight, flightKey: e.target.value})} required /></td>
                                    <td>{flight.departureAirportIataCode}</td>
                                    <td>{flight.arrivalAirportIataCode}</td>
                                    <td><input type="datetime-local" value={editedFlight.departureDate} onChange={(e) => setEditedFlight({...editedFlight, departureDate: e.target.value})} required /></td>
                                    <td><input type="datetime-local" value={editedFlight.arrivalDate} onChange={(e) => setEditedFlight({...editedFlight, arrivalDate: e.target.value})} required /></td>
                                    <td>
                                        <button onClick={handleSaveClick}>Save</button>
                                        <button onClick={() => setEditedFlight(null)}>Cancel</button>
                                    </td>
                                    </>
                                ) : (
                                    <>
                                    <td>{flight.flightNumber}</td>
                                    <td>{flight.flightKey}</td>
                                    <td>{flight.departureAirportIataCode}</td>
                                    <td>{flight.arrivalAirportIataCode}</td>
                                    <td>{flight.departureDate}</td>
                                    <td>{flight.arrivalDate}</td>
                                    <td>
                                        <button onClick={() => handleEditClick(flight)}>Edit</button>
                                        <button onClick={() => handleDeleteClick(flight.id)}>Delete</button>
                                    </td>
                                    </>
                                )
                            }
                        </tr>
                        ))
                    }
                </tbody>
            </table>
        </div>
    );
}

export default FlightManagement;
