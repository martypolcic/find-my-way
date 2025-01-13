import { useState } from 'react';
import './PickParticipantsForm.css';
import { FieldsState, FieldValue } from './SearchFlightsForm';


function PickParticipantsForm({ onSelect }: {onSelect: (field: keyof FieldsState, value: FieldValue) => void}) {
  const [participants, setParticipants] = useState<{ adults: number; children: number }>({
    adults: 1,
    children: 0,
  });

  function updateParticipants(type: 'adults' | 'children', operation: 'increment' | 'decrement') {
    setParticipants((prev) => ({
      ...prev,
      [type]: operation === 'increment' ? prev[type] + 1 : Math.max(type === 'adults' ? 1 : 0, prev[type] - 1),
    }));
  }

  function handleSubmit() {
    onSelect('passengers', participants);
  }

  return (
    <div className="participants-form">
      <h1>Travelling in a group?</h1>
      <div className="form-inputs">
        <div className="input-group">
          <label>Adults:</label>
          <div className="controls">
            <button onClick={() => updateParticipants('adults', 'decrement')}>-</button>
            <span>{participants.adults}</span>
            <button onClick={() => updateParticipants('adults', 'increment')}>+</button>
          </div>
        </div>
        <div className="input-group">
          <label>Children:</label>
          <div className="controls">
            <button onClick={() => updateParticipants('children', 'decrement')}>-</button>
            <span>{participants.children}</span>
            <button onClick={() => updateParticipants('children', 'increment')}>+</button>
          </div>
        </div>
      </div>

      <button onClick={handleSubmit} className="submit-button">
        Submit
      </button>
    </div>
  );
}

export default PickParticipantsForm;
