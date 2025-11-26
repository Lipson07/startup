
import { createSlice} from "@reduxjs/toolkit";
import type { PayloadAction } from "@reduxjs/toolkit";

interface UserState {
  name: string;
  email: string;
  password: string;
  verificationCode: string;
  isVerified: boolean;
  isSending: boolean;
}

const initialState: UserState = {
  name: "",
  email: "",
  password: "",
  verificationCode: "",
  isVerified: false,
  isSending: false,
};

const userSlice = createSlice({
  name: "user",
  initialState,
  reducers: {
    setName: (state: UserState, action: PayloadAction<string>) => {
      state.name = action.payload;
    },
    setEmail: (state: UserState, action: PayloadAction<string>) => {
      state.email = action.payload;
    },
    setPassword: (state: UserState, action: PayloadAction<string>) => {
      state.password = action.payload;
    },
    setUserData: (state: UserState, action: PayloadAction<{name: string; email: string; password: string}>) => {
      state.name = action.payload.name;
      state.email = action.payload.email;
      state.password = action.payload.password;
    },
    setVerificationCode: (state: UserState, action: PayloadAction<string>) => {
      state.verificationCode = action.payload;
    },
    setVerified: (state: UserState, action: PayloadAction<boolean>) => {
      state.isVerified = action.payload;
    },
    setSending: (state: UserState, action: PayloadAction<boolean>) => {
      state.isSending = action.payload;
    },
    resetUser: (state: UserState) => {
      return initialState;
    },
  },
});

export const { 
  setName, 
  setEmail, 
  setPassword, 
  setUserData,
  setVerificationCode, 
  setVerified, 
  setSending, 
  resetUser 
} = userSlice.actions;

export const selectUser = (state: { user: UserState }) => state.user;
export default userSlice.reducer;