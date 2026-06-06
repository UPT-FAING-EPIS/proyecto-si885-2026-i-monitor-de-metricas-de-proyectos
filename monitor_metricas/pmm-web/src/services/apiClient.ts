import axios from 'axios'

import { getSession, isSessionValid } from '../utils/session'

export const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:5000',
})

apiClient.interceptors.request.use((config) => {
  const session = getSession()
  if (isSessionValid(session)) {
    config.headers = config.headers ?? {}
    config.headers.Authorization = `Bearer ${session!.accessToken}`
  }
  return config
})

