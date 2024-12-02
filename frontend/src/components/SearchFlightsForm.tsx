import { useState } from "react";

interface FieldsState {
  from: string;
  date: string;
  passengers: number;
}

type FieldValue = FieldsState[keyof FieldsState];

function SearchFlightsForm() {
  const [fields, setFields] = useState<FieldsState>({
    from: "",
    date: "",
    passengers: 1,
  });

  function onSubmit(event: React.FormEvent) {
    event.preventDefault();
  }

  function handleChange(event: React.ChangeEvent<HTMLInputElement>) {
    const { name } = event.target;
    let value: FieldValue = event.target.value;

    if (name === "passengers") {
      value = Number.parseInt(value);
    }

    setFields({
      ...fields,
      [name]: value,
    });
  }

  return (
    <>
      <h2>Search Flights</h2>
      <form onSubmit={onSubmit} className="flex gap-8 max-w-32 p-8">
        <div>
          <label htmlFor="from">From</label>
          <input
            type="text"
            id="from"
            name="from"
            value={fields.from}
            onChange={handleChange}
          />
        </div>

        <div>
          <label htmlFor="date">Date</label>
          <input
            type="date"
            id="date"
            name="date"
            value={fields.date}
            onChange={handleChange}
          />
        </div>

        <div>
          <label htmlFor="passengers">Passengers</label>
          <input
            type="number"
            id="passengers"
            name="passengers"
            value={fields.passengers}
            onChange={handleChange}
          />
        </div>

        <button type="submit">Submit</button>
      </form>
    </>
  );
}

export default SearchFlightsForm;
