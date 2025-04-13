import apiClient from '../api/client';
import { createAsyncThunk } from '@reduxjs/toolkit';
import { SearchState, SearchResults } from '../types';

export const performSearch = createAsyncThunk(
  'search/perform',
  async (_, { getState, rejectWithValue }) => {
    const state = getState() as { search: SearchState };
    const { departureAirport, dateRange, passengers } = state.search;

    // Validate required parameters
    if (!departureAirport?.iataCode) {
      return rejectWithValue('Departure airport is required');
    }
    if (!dateRange[0] || !dateRange[1]) {
      return rejectWithValue('Valid date range is required');
    }
    if (passengers.adults <= 0 || passengers.rooms <= 0 || passengers.children < 0 || passengers.infants < 0) {
      return rejectWithValue('Invalid passenger count');
    }

    try {
      const params = new URLSearchParams({
        departureAirportIataCode: departureAirport.iataCode,
        departureDate: dateRange[0].toString(),
        returnDate: dateRange[1].toString(),
        adultCount: passengers.adults.toString(),
        childCount: passengers.children.toString(),
        infantCount: passengers.infants.toString(),
        roomCount: passengers.rooms.toString()
      });
      
      const response = await apiClient.get<{ data: SearchResults }>(
        `/api/v1/search-trips?${params.toString()}`
      );
      return response.data.data;
    } catch (error: any) {
      if (error.response?.status === 422) {
        return rejectWithValue(error.response.data.errors);
      }
      return rejectWithValue(error.response?.data?.message || error.message);
    }
  }
);