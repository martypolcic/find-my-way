import SearchSection from '../components/searchSection/SearchSection';
import SearchResultsContainer from '../components/searchResults/SearchResultsContainer';
import { useAppSelector } from '../store/hooks';
import { selectSearchParams } from '../store/searchSelectors';

function Home () {
    const searchParams = useAppSelector(selectSearchParams);
  return (
    <>
        {
        searchParams.departureAirport && searchParams.dateRange[0] && searchParams.dateRange[1] ? (
            <SearchResultsContainer />
        ) : (
            <SearchSection />
        )
        }
    </>
  );
}

export default Home;