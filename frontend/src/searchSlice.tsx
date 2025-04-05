import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { Airport } from "./types";
import { DateTime } from "luxon";

type SearchState = {
    departureAirport: Airport | null;
    dateRange: [DateTime | null, DateTime | null];
    passengers: { adults: number; children: number; infants: number; rooms: number };
};

const initialState: SearchState = {
    departureAirport: null,
    dateRange: [null, null],
    passengers: { adults: 1, children: 0, infants: 0, rooms: 1 },
};

const searchSlice = createSlice({
    name: "search",
    initialState,
    reducers: {
        setDepartureAirport: (state, action: PayloadAction<Airport | null>) => {
            state.departureAirport = action.payload;
        },
        setDateRange: (state, action: PayloadAction<[DateTime | null, DateTime | null]>) => {
            state.dateRange = action.payload;
        },
        setPassengers: (state, action: PayloadAction<{ adults: number; children: number; infants: number; rooms: number }>) => {
            state.passengers = action.payload;
        }
    },
});

export const { setDepartureAirport, setDateRange, setPassengers } = searchSlice.actions;
export default searchSlice.reducer;
