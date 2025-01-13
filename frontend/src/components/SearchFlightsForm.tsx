import { useState } from "react";

interface FieldsState {
  from: string;
  date: string;
  passengers: string;
}

type FieldValue = FieldsState[keyof FieldsState];

function SearchFlightsForm() {
  const [fields, setFields] = useState<FieldsState>({
    from: "",
    date: "",
    passengers: "1",
  });

  function onSubmit(event: React.FormEvent) {
    event.preventDefault();

    console.log(fields);

    const paramsObj = {
      departureAirportIataCode: fields.from,  //TODO: transform to IATA code
      departureDate: fields.date,
      passengerCount: fields.passengers,
    }
    const searchParams = new URLSearchParams(paramsObj);

    fetch(`http://localhost:81/api/v1/search-flights?${searchParams.toString()}`, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
      },
    })
      .then((response) => response.json())
      .then((data) => {
        console.log(data);
      });
  }

  function handleChange(event: React.ChangeEvent<HTMLInputElement>) {
    const { name } = event.target;
    let value: FieldValue = event.target.value;

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
            required
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
            required
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
            required
          />
        </div>

        <button type="submit">Submit</button>
      </form>
    </>
  );
}

export default SearchFlightsForm;
