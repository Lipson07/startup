
import { configureStore } from "@reduxjs/toolkit";
import { persistStore, persistReducer } from "redux-persist";
import storage from "redux-persist/lib/storage";
import user from "./user";

const persistUser = {
  key: "user",
  storage,
  whitelist: ["name", "email", "password", "isVerified"],
};

const persistUserReducer = persistReducer(persistUser, user);

const store = configureStore({
  reducer: {
    user: persistUserReducer,
  },
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;
export const persistor = persistStore(store);
export default store;