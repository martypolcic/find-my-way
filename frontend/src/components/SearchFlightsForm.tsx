import { useState } from "react";
import "./SearchFlightsForm.css";
import PickAirportForm from "./PickAirportForm";
import PickDateForm from "./PickDateForm";
import PickParticipantsForm from "./PickParticipantsForm";
import SearchResults from "./SearchResults";

export interface FieldsState {
  from: Airport | null;
  date: string | null;
  passengers: { adults: number; children: number } | null;
}

export type FieldValue = FieldsState[keyof FieldsState];

export interface Airport {
  id: number;
  airportName: string;
  iataCode: string;
  cityName: string;
  countryName: string;
}

function SearchFlightsForm() {
  const [activeStep, setActiveStep] = useState("");
  const [fields, setFields] = useState<FieldsState>({
    from: null,
    date: null,
    passengers: null,
  });

  function handleFieldChange(field: keyof FieldsState, value: FieldValue) {
    setFields((prevFields) => ({
      ...prevFields,
      [field]: value,
    }));

    setActiveStep("");
  }

  function handleStepButtonClick(step: string) {
    setActiveStep(step);
  }

  return (
    <div className="search-flights-form">
      <h1>Your adventure starts here</h1>
      <p>Let's look for best trip deals</p>
      <div className="inputs">
        <button 
          onClick={() => handleStepButtonClick('Airport')}
          className={fields.from != null ? 'completed' : ''}
        >
          Departure Airport 
          {fields.from != null && <span>{fields.from.cityName}</span>}
        </button>

        <button 
          onClick={() => handleStepButtonClick('Date')}
          className={fields.date != null ? 'completed' : ''}
        >
          Departure Date
          {fields.date != null && <span>{fields.date}</span>}
        </button>

        <button 
          onClick={() => handleStepButtonClick('Participants')}
          className={fields.passengers != null ? 'completed' : ''}
        >
          Number of participants
          {fields.passengers != null && <span>{fields.passengers.adults} adults, {fields.passengers.children} children</span>}
        </button>
      </div>
      {
        activeStep === 'Airport' && 
        <PickAirportForm 
          onSelect={handleFieldChange}/>
      }
      {
        activeStep === 'Date' && 
        <PickDateForm 
          onSelect={handleFieldChange}/>
      }
      {
        activeStep === 'Participants' && 
        <PickParticipantsForm 
          onSelect={handleFieldChange} />
      }

      {
        fields.from != null && fields.date != null && fields.passengers != null && 
        <SearchResults 
          from={fields.from} 
          date={fields.date} 
          passengers={fields.passengers} />
      }
      
    </div>
  );
}

export default SearchFlightsForm;
