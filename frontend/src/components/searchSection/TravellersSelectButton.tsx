import { useState, useRef, useEffect } from "react";
import { useDispatch, useSelector } from "react-redux";
import { RootState } from "../../store";
import { setPassengers } from "../../searchSlice";
import { motion } from "framer-motion";

const TravellerSelector = () => {
    const dispatch = useDispatch();
    const passengers = useSelector((state: RootState) => state.search.passengers);
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
        if ((type === "adults" && value <= 0) || (type === "rooms" && value <= 0)) {
            return;
        }
        dispatch(setPassengers({ ...passengers, [type]: Math.max(0, value) }));
    };

    return (
        <div className="relative" ref={dropdownRef}>
            <button className="styled-button min-w-[7.5em]" onClick={() => setIsOpen(!isOpen)}>
                {`${passengers.adults + passengers.children + passengers.infants} Traveller${
                    passengers.adults + passengers.children + passengers.infants > 1 ? "s" : ""
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
                            <button className="control-button" onClick={() => updatePassengers("adults", passengers.adults - 1)}>-</button>
                            <span className="control-label">{passengers.adults}</span>
                            <button className="control-button" onClick={() => updatePassengers("adults", passengers.adults + 1)}>+</button>
                        </div>
                    </div>
                    <div className="dropdown-item">
                        <span>Children</span>
                        <div className="item-controls">
                            <button className="control-button" onClick={() => updatePassengers("children", passengers.children - 1)}>-</button>
                            <span className="control-label">{passengers.children}</span>
                            <button className="control-button" onClick={() => updatePassengers("children", passengers.children + 1)}>+</button>
                        </div>
                    </div>
                    <div className="dropdown-item">
                        <span>Infants</span>
                        <div className="item-controls">
                            <button className="control-button" onClick={() => updatePassengers("infants", passengers.infants - 1)}>-</button>
                            <span className="control-label">{passengers.infants}</span>
                            <button className="control-button" onClick={() => updatePassengers("infants", passengers.infants + 1)}>+</button>
                        </div>
                    </div>
                    <div className="dropdown-item">
                        <span>Rooms</span>
                        <div className="item-controls">
                            <button className="control-button" onClick={() => updatePassengers("rooms", passengers.rooms - 1)}>-</button>
                            <span className="control-label">{passengers.rooms}</span>
                            <button className="control-button" onClick={() => updatePassengers("rooms", passengers.rooms + 1)}>+</button>
                        </div>
                    </div>
                </motion.div>
            )}
        </div>
    );
};

export default TravellerSelector;
