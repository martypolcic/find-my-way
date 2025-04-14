import { useAppDispatch } from '../../store/hooks';
import { performSearch } from '../../store/searchThunks';
import { setDepartureAirport, setDateRange, setPassengers } from '../../store/searchSlice';
import { Airport } from '../../types';
import { DateTime } from 'luxon';

interface SearchButtonProps {
  searchParams: {
    departureAirport: Airport | null;
    dateRange: [string | null, string | null];
    passengers: {
      adults: number;
      children: number;
      infants: number;
      rooms: number;
    };
  };
}

const SearchButton = ({ searchParams }: SearchButtonProps) => {
    const dispatch = useAppDispatch();

    const handleSearch = () => {
        if (
            !searchParams.departureAirport?.iataCode || 
            !searchParams.dateRange[0] ||
            !searchParams.dateRange[1] ||
            searchParams.passengers.adults <= 0 ||
            searchParams.passengers.children < 0 ||
            searchParams.passengers.infants < 0 ||
            searchParams.passengers.rooms < 0
        ) {
            alert('Please fill in all fields');
            return;
        }

        dispatch(setDepartureAirport(searchParams.departureAirport));
        dispatch(setDateRange([
            searchParams.dateRange[0] ? DateTime.fromISO(searchParams.dateRange[0]).toFormat('yyyy-MM-dd').toString() : null,
            searchParams.dateRange[1] ? DateTime.fromISO(searchParams.dateRange[1]).toFormat('yyyy-MM-dd').toString() : null,
        ]));
        dispatch(setPassengers(searchParams.passengers));
        
        dispatch(performSearch());
    };

    return (
        <button
            onClick={handleSearch}
            className="styled-button submit-button"
        >
            Search
        </button>
    );
};

export default SearchButton;