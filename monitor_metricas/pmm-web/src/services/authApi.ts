import type {
  AuthResponse,
  ForgotPasswordRequest,
  GoogleLoginRequest,
  LoginRequest,
  RegisterRequest,
} from '../types/auth'
import { apiClient } from './apiClient'

export async function register(payload: RegisterRequest) {
  const { data } = await apiClient.post<AuthResponse>('/api/auth/register', payload)
  return data
}

export async function login(payload: LoginRequest) {
  const { data } = await apiClient.post<AuthResponse>('/api/auth/login', payload)
  return data
}

export async function loginWithGoogle(payload: GoogleLoginRequest) {
  const { data } = await apiClient.post<AuthResponse>('/api/auth/google', payload)
  return data
}

export async function forgotPassword(payload: ForgotPasswordRequest) {
  await apiClient.post('/api/auth/forgot-password', payload)
}

export async function logout() {
  await apiClient.post('/api/auth/logout')
}

