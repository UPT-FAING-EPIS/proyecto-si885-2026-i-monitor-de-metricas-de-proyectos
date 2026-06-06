export type User = {
  id: string
  fullName: string
  email: string
}

export type AuthResponse = {
  accessToken: string
  user: User
  expiration: string
}

export type RegisterRequest = {
  fullName: string
  email: string
  password: string
}

export type LoginRequest = {
  email: string
  password: string
}

export type GoogleLoginRequest = {
  supabaseAccessToken: string
}

export type ForgotPasswordRequest = {
  email: string
}

