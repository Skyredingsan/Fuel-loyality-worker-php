import { useState } from 'react';
import { Link, useNavigate, Outlet } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

const navItems = [
    { path: '/dashboard', label: 'Главная', roles: ['tm', 'expert', 'coordinator'] },
    { path: '/results', label: 'Мои результаты', roles: ['tm'] },
    { path: '/enter-results', label: 'Ввод результатов', roles: ['expert', 'coordinator'] },
    { path: '/all-results', label: 'Все результаты', roles: ['coordinator'] },
    { path: '/users', label: 'Пользователи', roles: ['coordinator'] },
    { path: '/kpi', label: 'KPI', roles: ['coordinator'] },
    { path: '/reports', label: 'Отчёты', roles: ['coordinator'] },
];

export const Layout: React.FC = () => {
    const { user, logout } = useAuth();
    const navigate = useNavigate();
    const [sidebarOpen, setSidebarOpen] = useState(false);

    const handleLogout = () => {
        logout();
        navigate('/login');
    };

    return (
        <div className="min-h-screen bg-gray-100 flex">
            {/* Sidebar */}
            <aside
                className={`fixed lg:static inset-y-0 left-0 w-64 bg-gray-900 text-white transform ${
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full'
                } lg:translate-x-0 transition-transform duration-200 ease-in-out z-50`}
            >
                <div className="p-6 border-b border-gray-800">
                    <h1 className="text-xl font-bold">⛽ Топливный Альянс</h1>
                    <p className="text-xs text-gray-400 mt-1">Программа лояльности</p>
                </div>
                <nav className="p-4 space-y-1">
                    {navItems
                        .filter((item) => user && item.roles.includes(user.role))
                        .map((item) => (
                            <Link
                                key={item.path}
                                to={item.path}
                                onClick={() => setSidebarOpen(false)}
                                className="block px-4 py-2 rounded text-gray-300 hover:bg-gray-800 hover:text-white transition-colors"
                            >
                                {item.label}
                            </Link>
                        ))}
                </nav>
            </aside>

            {/* Overlay для мобильного */}
            {sidebarOpen && (
                <div
                    className="fixed inset-0 bg-black opacity-50 z-40 lg:hidden"
                    onClick={() => setSidebarOpen(false)}
                />
            )}

            {/* Main content */}
            <div className="flex-1 flex flex-col lg:ml-0">
                <header className="bg-white shadow-sm border-b">
                    <div className="flex items-center justify-between px-6 py-4">
                        <button
                            onClick={() => setSidebarOpen(!sidebarOpen)}
                            className="lg:hidden text-gray-500"
                        >
                            ☰
                        </button>
                        <div className="flex items-center gap-4 ml-auto">
                            <div className="text-right">
                                <div className="text-sm font-medium text-gray-900">{user?.fio}</div>
                                <div className="text-xs text-gray-500">{user?.role_label}</div>
                            </div>
                            <button
                                onClick={handleLogout}
                                className="px-4 py-2 text-sm text-white bg-red-500 rounded hover:bg-red-600"
                            >
                                Выйти
                            </button>
                        </div>
                    </div>
                </header>

                <main className="flex-1 p-6">
                    <Outlet />
                </main>
            </div>
        </div>
    );
};