import { useEffect, useState } from 'react';
import { Airport } from './SearchFlightsForm';
import './SearchResults.css';

function SearchResults({from, date, passengers}: {from: Airport | null, date: string, passengers: { adults: number; children: number }}) {
    const [flights, setFlights] = useState<any[]>([]);

    useEffect(() => {
        const fetchFlights = async () => {
            const response = await fetch(`http://localhost:81/api/v1/search-flights?departureAirportIataCode=${from?.iataCode}&departureDate=${date}&passengerCount=${passengers.adults + passengers.children}`);
            const data = await response.json();
            setFlights(data.data);
        }

        fetchFlights();
        console.log(flights);
    }
    , [from, date, passengers]);

    const formatDate = (date: string) => {
        const dateObj = new Date(date);
        return `${dateObj.getDate()}.${dateObj.getMonth() + 1}.${dateObj.getFullYear()} ${addLeadingZeros(dateObj.getHours())}:${addLeadingZeros(dateObj.getMinutes())}`;
    }

    const addLeadingZeros = (num: number) => {
        return num < 10 ? `0${num}` : num;
    }

    return (
        <div className='search-results'>
            <h1>Your vacation options</h1>
            <div className="flights">
                {
                    flights && flights.length > 0 && flights.map((flight) => (
                        <div key={flight.id} className="flight">
                            <h2>{flight.arrivalAirport.country_name} - {flight.arrivalAirport.city_name}</h2>
                            <p> {formatDate(flight.departureDate)}</p>
                            <p className='flight-price'>{flight.flightPrices[0].price_value} {flight.flightPrices[0].currency_code}</p>
                        </div>
                    ))

                    
                }
            </div>
        </div>
    );
}

export default SearchResults;