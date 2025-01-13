import { useState } from "react";
import './PickAirportForm.css';
import { FieldsState, FieldValue, Airport } from "./SearchFlightsForm";
import CountryCard from "./CountryCard";
import axios from "axios";

function PickAirportForm({ onSelect }: { onSelect: (field: keyof FieldsState, value: FieldValue) => void }) {
  const [groupedAirports, setGroupedAirports] = useState<Record<string, Airport[]>>({});
  const [expandedCountries, setExpandedCountries] = useState<Set<string>>(new Set());

  function handleInputChange(event: React.ChangeEvent<HTMLInputElement>) {
    const { value } = event.target;
    if (value.length < 1) {
      setGroupedAirports({});
      return;
    }

  const fetchData = async () => {
    try {
      const response = await axios.get(`http://localhost:81/api/v1/search-airports?search=${value}`);
      const json = response.data;

      const groups = json.data.reduce((groups: Record<string, Airport[]>, airport: Airport) => {
        const group = airport.countryName;

        groups[group] ??= [];
        groups[group].push(airport);

        return groups;
      }, {});

      setGroupedAirports(groups);
    } catch (error) {
      // Handle error
    }
  }

  fetchData(); 
}

  function handleAirportClick(airport: Airport) {
    onSelect('from', airport);
  }

  function toggleShowMore(country: string) {
    setExpandedCountries((prev) => {
      const newSet = new Set(prev);
      if (newSet.has(country)) {
        newSet.delete(country);
      } else {
        newSet.add(country);
      }
      return newSet;
    });
  }

  return (
    <div className="airport-picker">
      <h1>Where do you want to fly from?</h1>
      <input 
        type="text"
        placeholder="Enter country, city or airport"
        onChange={handleInputChange}
      />
      
      <div className="airport-list">
        {Object.keys(groupedAirports)
          .slice(0, 12)
          .map((group) => (
            <CountryCard
              key={group}
              country={group}
              airports={groupedAirports[group]}
              isExpanded={expandedCountries.has(group)}
              onToggle={() => toggleShowMore(group)}
              onAirportClick={handleAirportClick}
            />
          ))}
      </div>
    </div>
  );
}

export default PickAirportForm;
