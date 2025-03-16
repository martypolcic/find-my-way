import "react-calendar/dist/Calendar.css";
import "./SearchSection.css";
import AirportSearchButton from "./AirportSelectButton";
import DateSelectButton from "./DateSelectButton";
import TravellerSelectorButton from "./TravellersSelectButton";

const SearchSection = () => {

    return (
        <div className="relative flex flex-row justify-center items-center gap-4 p-4 shadow-lg rounded-xl">
            {/* TODO: Labels are not responding to clicking */}
            <AirportSearchButton />
            <DateSelectButton />
            <TravellerSelectorButton />

            {/* TODO: implement search destinations logic on button click */}
            <button className="styled-button submit-button">
                Search
            </button>
        </div>
    );
};

export default SearchSection;
