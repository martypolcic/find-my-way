import { useState, useEffect, useRef } from "react";
import { motion } from "framer-motion";
import SearchResult from "./SearchResult";
import { Airport } from "../../types";
import axios from "axios";

interface AirportSelectButtonProps {
  selectedAirport: Airport | null;
  onAirportChange: (airport: Airport | null) => void;
}

const AirportSelectButton = ({ selectedAirport, onAirportChange }: AirportSelectButtonProps) => {
    const [input, setInput] = useState("");
    const [airportResults, setAirportResults] = useState<Airport[]>([]);
    const [message, setMessage] = useState("");
    const [isOpen, setIsOpen] = useState(false);
    const dropdownRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const fetchAirports = async () => {
            if (input.length > 2) {
                try {
                    const response = await axios.get(`http://localhost:8001/api/v1/search-airports?query=${input}`);
                    setAirportResults(response.data.data);
                    if (response.data.data.length === 0) setMessage("No airports found");
                } catch (error) {
                    console.error("Error fetching airports:", error);
                    setMessage("Error fetching airports");
                }
            } else {
                setMessage("Search by city, country or airport name");
                setAirportResults([]);
            }
        };
        
        fetchAirports();
    }, [input]);

    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
                setIsOpen(false);
            }
        };
        
        document.addEventListener("mousedown", handleClickOutside);
        return () => {
            document.removeEventListener("mousedown", handleClickOutside);
        };
    }, []);

    const handleAirportSelect = (airport: Airport) => {
        onAirportChange(airport);
        setIsOpen(false);
    };

    return (
        <div className="relative" ref={dropdownRef}>
            <input
                type="text"
                className={`styled-button min-w-[20em] ${selectedAirport && !isOpen ? "filled" : ""}`}
                placeholder="Leaving from?"
                value={ selectedAirport && !isOpen ? selectedAirport.name : input }
                onChange={(e) => setInput(e.target.value)}
                onFocus={() => setIsOpen(true)}
            />
            {selectedAirport && !isOpen && (
                <>
                    <label className="floating-label">Leaving from</label>
                </>
            )}
            {isOpen && (
                <motion.div className="dropdown" initial={{ opacity: 0, y: -10 }} animate={{ opacity: 1, y: 0 }}>
                    {airportResults.length === 0 
                    ? <div className="message">{message}</div>
                    : airportResults.map((airport) => (
                        <SearchResult
                            key={airport.id}
                            airport={airport}
                            onClick={() => handleAirportSelect(airport)}
                        />
                    ))}
                </motion.div>
            )}
        </div>
    );
};

export default AirportSelectButton;