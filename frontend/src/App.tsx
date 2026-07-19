import { Routes, Route, Navigate } from 'react-router-dom';
import { useAuth } from './contexts/AuthContext';
import { Layout } from './components/Layout';
import { ProtectedRoute } from './components/ProtectedRoute';
import { Login } from './pages/Login';
import { Dashboard } from './pages/Dashboard';
import { Results } from './pages/Results';
import { EnterResults } from './pages/EnterResults';
import { AllResults } from './pages/AllResults';
import { Users } from './pages/Users';
import { KpiEditor } from './pages/KpiEditor';
import { Reports } from './pages/Reports';

export default function App() {
    return (
        <Routes>
            <Route path="/login" element={<Login />} />

            {/* Все защищённые маршруты используют общий Layout через Outlet */}
            <Route
                element={
                    <ProtectedRoute>
                        <Layout />
                    </ProtectedRoute>
                }
            >
                <Route path="/dashboard" element={<Dashboard />} />
                <Route
                    path="/results"
                    element={
                        <ProtectedRoute roles={['tm']}>
                            <Results />
                        </ProtectedRoute>
                    }
                />
                <Route
                    path="/enter-results"
                    element={
                        <ProtectedRoute roles={['expert', 'coordinator']}>
                            <EnterResults />
                        </ProtectedRoute>
                    }
                />
                <Route
                    path="/all-results"
                    element={
                        <ProtectedRoute roles={['coordinator']}>
                            <AllResults />
                        </ProtectedRoute>
                    }
                />
                <Route
                    path="/users"
                    element={
                        <ProtectedRoute roles={['coordinator']}>
                            <Users />
                        </ProtectedRoute>
                    }
                />
                <Route
                    path="/kpi"
                    element={
                        <ProtectedRoute roles={['coordinator']}>
                            <KpiEditor />
                        </ProtectedRoute>
                    }
                />
                <Route
                    path="/reports"
                    element={
                        <ProtectedRoute roles={['coordinator']}>
                            <Reports />
                        </ProtectedRoute>
                    }
                />
            </Route>

            <Route path="*" element={<Navigate to="/dashboard" replace />} />
        </Routes>
    );
}