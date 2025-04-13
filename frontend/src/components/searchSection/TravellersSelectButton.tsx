import { useState, useRef, useEffect } from "react";
import { motion } from "framer-motion";

interface TravellerSelectorProps {
  selectedPassengers: {
    adults: number;
    children: number;
    infants: number;
    rooms: number;
  };
  onPassengersChange: (passengers: {
    adults: number;
    children: number;
    infants: number;
    rooms: number;
  }) => void;
}

const TravellerSelector = ({ selectedPassengers, onPassengersChange }: TravellerSelectorProps) => {
    const [isOpen, setIsOpen] = useState(false);
    const dropdownRef = useRef<HTMLDivElement>(null);

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

    const updatePassengers = (type: "adults" | "children" | "infants" | "rooms", value: number) => {
        if (
            (type === 'adults' && value <= 0) ||
            (type === 'rooms' && value <= 0)
        ) {
            return;
        }

        const newPassengers = { ...selectedPassengers, [type]: Math.max(0, value) };
        onPassengersChange(newPassengers);
    };

    return (
        <div className="relative" ref={dropdownRef}>
            <button className="styled-button min-w-[7.5em]" onClick={() => setIsOpen(!isOpen)}>
                {`${selectedPassengers.adults + selectedPassengers.children + selectedPassengers.infants} Traveller${
                    selectedPassengers.adults + selectedPassengers.children + selectedPassengers.infants > 1 ? "s" : ""
                }`}
            </button>

            {isOpen && (
                <motion.div
                    className="dropdown"
                    initial={{ opacity: 0, y: -10 }}
                    animate={{ opacity: 1, y: 0 }}
                >
                    <div className="dropdown-item">
                        <span>Adults</span>
                        <div className="item-controls">
                            <button className="control-button" onClick={() => updatePassengers("adults", selectedPassengers.adults - 1)}>-</button>
                            <span className="control-label">{selectedPassengers.adults}</span>
                            <button className="control-button" onClick={() => updatePassengers("adults", selectedPassengers.adults + 1)}>+</button>
                        </div>
                    </div>
                    <div className="dropdown-item">
                        <span>Children</span>
                        <div className="item-controls">
                            <button className="control-button" onClick={() => updatePassengers("children", selectedPassengers.children - 1)}>-</button>
                            <span className="control-label">{selectedPassengers.children}</span>
                            <button className="control-button" onClick={() => updatePassengers("children", selectedPassengers.children + 1)}>+</button>
                        </div>
                    </div>
                    <div className="dropdown-item">
                        <span>Infants</span>
                        <div className="item-controls">
                            <button className="control-button" onClick={() => updatePassengers("infants", selectedPassengers.infants - 1)}>-</button>
                            <span className="control-label">{selectedPassengers.infants}</span>
                            <button className="control-button" onClick={() => updatePassengers("infants", selectedPassengers.infants + 1)}>+</button>
                        </div>
                    </div>
                    <div className="dropdown-item">
                        <span>Rooms</span>
                        <div className="item-controls">
                            <button className="control-button" onClick={() => updatePassengers("rooms", selectedPassengers.rooms - 1)}>-</button>
                            <span className="control-label">{selectedPassengers.rooms}</span>
                            <button className="control-button" onClick={() => updatePassengers("rooms", selectedPassengers.rooms + 1)}>+</button>
                        </div>
                    </div>
                </motion.div>
            )}
        </div>
    );
};

export default TravellerSelector;