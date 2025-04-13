import { useState, useRef, useEffect } from "react";
import { motion } from "framer-motion";
import Calendar from "react-calendar";
import "../../styles/Calendar.css";
import { DateTime } from "luxon";

interface DateSelectButtonProps {
  selectedDates: [string | null, string | null];
  onDateChange: (dates: [string | null, string | null]) => void;
}

const DateSelectButton = ({ selectedDates, onDateChange }: DateSelectButtonProps) => {
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

    const handleDateChange = (dates: any) => {
        const isoDates = dates.map((date: Date) => 
            date ? DateTime.fromJSDate(date).toISODate() : null
        ) as [string | null, string | null];
        onDateChange(isoDates);
    };

    return (
        <div className="relative flex flex-row gap-4" ref={dropdownRef}>
            <div className="relative">
                <button 
                    className={`styled-button min-w-[5em] ${selectedDates[0] ? 'filled' : ""}`} 
                    onClick={() => setIsOpen(!isOpen)}
                >
                    {selectedDates[0] ? DateTime.fromISO(selectedDates[0]).toFormat("dd MMM") : "Depart"}
                </button>
                {selectedDates[0] && <label className="floating-label">Depart</label>}
            </div>
            
            <div className="relative">
                <button 
                    className={`styled-button min-w-[5em] ${selectedDates[1] ? 'filled' : ""}`} 
                    onClick={() => setIsOpen(!isOpen)}
                >
                    {selectedDates[1] ? DateTime.fromISO(selectedDates[1]).toFormat("dd MMM") : "Return"}
                </button>
                {selectedDates[1] && <label className="floating-label">Return</label>}
            </div>

            {isOpen && (
                <motion.div className="dropdown" initial={{ opacity: 0, y: -10 }} animate={{ opacity: 1, y: 0 }}>
                    <Calendar
                        selectRange={true}
                        onChange={handleDateChange}
                        value={selectedDates.map(date => 
                            date ? DateTime.fromISO(date).toJSDate() : null
                        ) as [Date | null, Date | null]}
                        minDate={new Date()}
                        allowPartialRange={true}
                        minDetail="decade"
                    />
                </motion.div>
            )}
        </div>
    );
};

export default DateSelectButton;