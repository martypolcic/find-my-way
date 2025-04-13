import { configureStore } from "@reduxjs/toolkit";
import searchReducer from "../store/searchSlice";
import selectionsReducer from "../store/selectionSlice";

export const store = configureStore({
    reducer: {
        search: searchReducer,
        selections: selectionsReducer,
    },
    devTools: true,
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;
