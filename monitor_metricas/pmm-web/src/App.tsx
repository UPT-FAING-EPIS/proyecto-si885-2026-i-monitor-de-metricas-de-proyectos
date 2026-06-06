import { BrowserRouter, Navigate, Route, Routes, useLocation } from 'react-router-dom'

import { AppShell } from './layouts/AppShell'
import { AdvancedAnalyticsPage } from './pages/AdvancedAnalyticsPage'
import { AlertsPage } from './pages/AlertsPage'
import { DashboardPage } from './pages/DashboardPage'
import { ForgotPasswordPage } from './pages/ForgotPasswordPage'
import { LoginPage } from './pages/LoginPage'
import { PowerBiEmbeddedPage } from './pages/PowerBiEmbeddedPage'
import { ProjectDetailPage } from './pages/ProjectDetailPage'
import { ProjectsPage } from './pages/ProjectsPage'
import { RegisterPage } from './pages/RegisterPage'
import { SettingsPage } from './pages/SettingsPage'
import { TrelloIntegrationPage } from './pages/TrelloIntegrationPage'
import { clearSession, getSession, isSessionValid } from './utils/session'

function isAuthed() {
  return isSessionValid(getSession())
}

function RequireAuth({ children }: { children: React.ReactNode }) {
  const location = useLocation()
  if (!isAuthed()) {
    clearSession()
    return <Navigate to="/login" replace state={{ from: location.pathname }} />
  }
  return <>{children}</>
}

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Navigate to="/login" replace />} />
        <Route path="/login" element={isAuthed() ? <Navigate to="/dashboard" replace /> : <LoginPage />} />
        <Route path="/register" element={isAuthed() ? <Navigate to="/dashboard" replace /> : <RegisterPage />} />
        <Route
          path="/forgot-password"
          element={isAuthed() ? <Navigate to="/dashboard" replace /> : <ForgotPasswordPage />}
        />
        <Route
          element={
            <RequireAuth>
              <AppShell />
            </RequireAuth>
          }
        >
          <Route path="/dashboard" element={<DashboardPage />} />
          <Route path="/projects" element={<ProjectsPage />} />
          <Route path="/projects/:id" element={<ProjectDetailPage />} />
          <Route path="/analytics" element={<AdvancedAnalyticsPage />} />
          <Route path="/alerts" element={<AlertsPage />} />
          <Route path="/integrations/trello" element={<TrelloIntegrationPage />} />
          <Route path="/powerbi" element={<PowerBiEmbeddedPage />} />
          <Route path="/settings" element={<SettingsPage />} />
        </Route>
        <Route path="*" element={<Navigate to="/login" replace />} />
      </Routes>
    </BrowserRouter>
  )
}
