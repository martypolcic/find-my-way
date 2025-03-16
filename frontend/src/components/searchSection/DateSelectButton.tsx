import { useState, useRef, useEffect } from "react";
import { useDispatch, useSelector } from "react-redux";
import { RootState } from "../../store";
import { setDateRange } from "../../searchSlice";
import { motion } from "framer-motion";
import Calendar from "react-calendar";
import "../../styles/Calendar.css";
import { DateTime } from "luxon";

//TODO: refactor this component to offer one or two buttons 
const DateSelectorButton = () => {
    const dispatch = useDispatch();
    const dateRange = useSelector((state: RootState) => state.search.dateRange);
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
        //Convert to ISO string - timezone independent
        dispatch(setDateRange(dates.map((date: Date) => DateTime.fromJSDate(date).toISODate())));
    };

    return (
        <div className="relative flex flex-row gap-4" ref={dropdownRef}>
            {/* TODO: Load Buttons based on fields in redux - reduce duplication */}
            <div className="relative">
                <button className={`styled-button min-w-[5em] ${dateRange[0] ? 'filled' : ""}`} onClick={() => setIsOpen(!isOpen)}>
                    {
                        dateRange[0] ? DateTime.fromISO(dateRange[0].toString()).toFormat("dd MMM") : "Depart"
                    }
                </button>
                {
                    dateRange[0] && <label className="floating-label">Depart</label>
                }
            </div>
            
            <div className="relative">
                <button className={`styled-button min-w-[5em] ${dateRange[1] ? 'filled' : ""}`} onClick={() => setIsOpen(!isOpen)}>
                    {
                        dateRange[1] ? DateTime.fromISO(dateRange[1].toString()).toFormat("dd MMM") : "Return"
                    }
                </button>
                {
                    dateRange[1] && <label className="floating-label">Return</label>
                }

            </div>
            {isOpen && (
                <motion.div className="dropdown" initial={{ opacity: 0, y: -10 }} animate={{ opacity: 1, y: 0 }}>
                    <Calendar
                        selectRange={true}
                        onChange={handleDateChange}
                        value={dateRange.map((date) => date ? DateTime.fromISO(date.toString()).toJSDate() : null) as [Date | null, Date | null]}
                        minDate={new Date()}
                        allowPartialRange={true}
                        minDetail="decade"
                    />
                </motion.div>
            )}
        </div>
    );
};

export default DateSelectorButton;
