import type { AuthResponse } from '../types/auth'

export type Session = AuthResponse

const KEY = 'pmm_session'

function readFrom(storage: Storage): Session | null {
  const raw = storage.getItem(KEY)
  if (!raw) return null
  try {
    return JSON.parse(raw) as Session
  } catch {
    return null
  }
}

export function getSession(): Session | null {
  return readFrom(localStorage) ?? readFrom(sessionStorage)
}

export function setSession(session: Session, remember: boolean) {
  localStorage.removeItem(KEY)
  sessionStorage.removeItem(KEY)
  const storage = remember ? localStorage : sessionStorage
  storage.setItem(KEY, JSON.stringify(session))
}

export function clearSession() {
  localStorage.removeItem(KEY)
  sessionStorage.removeItem(KEY)
}

export function isSessionValid(session: Session | null) {
  if (!session?.accessToken || !session.expiration) return false
  const expiresAt = Date.parse(session.expiration)
  if (Number.isNaN(expiresAt)) return false
  return Date.now() < expiresAt
}

