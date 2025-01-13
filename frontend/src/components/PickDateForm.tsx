import { useState } from 'react';
import Calendar from "./Calendar";
import "./PickDateForm.css";
import { FieldsState, FieldValue } from "./SearchFlightsForm";

function PickDateForm({onSelect}: {onSelect: (field: keyof FieldsState, value: FieldValue) => void}) {
  const [date, setDate] = useState<any>(new Date());

  function handleSubmit() {
    onSelect('date', date.toISOString().split('T')[0]);
  }

  return (
    <div className='date-picker'>
        <h1>When do you want to leave?</h1>
        <Calendar
        date={date}
        setDate={setDate}
        />

        <button 
          onClick={handleSubmit}
          className='submit-button'
        >
          Submit
        </button>
    </div>
  );
}

export default PickDateForm;