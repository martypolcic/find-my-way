import './SearchResult.css';
import { Airport } from '../../types';

function SearchResult ({ airport, onClick} : { airport: Airport, onClick: () => void }) {
    
    return (
        <div className="search-result" onClick={onClick}>
            <h1>{airport.name}</h1>
            <h2>{airport.country} - {airport.city}</h2>
        </div>
    )
}

export default SearchResult