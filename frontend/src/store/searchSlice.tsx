import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { Airport, SearchState } from "../types";
import { DateTime } from "luxon";
import { performSearch } from "./searchThunks";

const initialState: SearchState = {
    departureAirport: null,
    dateRange: [null, null],
    passengers: { adults: 1, children: 0, infants: 0, rooms: 1 },
    requestState: {
        status: "idle",
        error: null,
    },
    results: null,
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
        },
        resetRequestState: (state) => {
            state.requestState = initialState.requestState;
            state.results = null;
        }
    },
    extraReducers: (builder) => {
        builder
          .addCase(performSearch.pending, (state) => {
            state.requestState.status = 'loading';
            state.requestState.error = null;
          })
          .addCase(performSearch.fulfilled, (state, action) => {
            state.requestState.status = 'succeeded';
            state.results = action.payload;
          })
          .addCase(performSearch.rejected, (state, action) => {
            state.requestState.status = 'failed';
            state.requestState.error = action.payload as string;
          });
      }
});

export const { 
    setDepartureAirport,
    setDateRange,
    setPassengers,
    resetRequestState
} = searchSlice.actions;
export default searchSlice.reducer;
