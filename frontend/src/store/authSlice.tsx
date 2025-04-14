import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import apiClient from '../api/client';
import { RootState } from './store';

interface AuthState {
  isAuthenticated: boolean;
  loading: boolean;
}

const initialState: AuthState = {
  isAuthenticated: false,
  loading: true,
};

export const fetchUser = createAsyncThunk('auth/fetchUser', async (_, { rejectWithValue }) => {
  try {
    await apiClient.get('/api/user');
    return true;
  } catch {
    return rejectWithValue(false);
  }
});

const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    setAuth(state, action) {
      state.isAuthenticated = action.payload;
    },
    logout(state) {
      state.isAuthenticated = false;
    }
  },
  extraReducers: builder => {
    builder
      .addCase(fetchUser.pending, state => {
        state.loading = true;
      })
      .addCase(fetchUser.fulfilled, state => {
        state.isAuthenticated = true;
        state.loading = false;
      })
      .addCase(fetchUser.rejected, state => {
        state.isAuthenticated = false;
        state.loading = false;
      });
  }
});

export const selectIsAuthenticated = (state: RootState) => state.auth.isAuthenticated;
export const { setAuth, logout } = authSlice.actions;
export default authSlice.reducer;
