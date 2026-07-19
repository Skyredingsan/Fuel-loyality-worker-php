import { useEffect, useState } from 'react';
import { userService } from '../services/users';
import type { User, UserRole } from '../types';

export const Users: React.FC = () => {
    const [users, setUsers] = useState<User[]>([]);
    const [loading, setLoading] = useState(true);
    const [showForm, setShowForm] = useState(false);
    const [editingUser, setEditingUser] = useState<User | null>(null);

    const load = () => {
        setLoading(true);
        userService
            .getAll()
            .then(setUsers)
            .catch(console.error)
            .finally(() => setLoading(false));
    };

    useEffect(() => {
        load();
    }, []);

    const handleDelete = async (id: number) => {
        if (!confirm('Удалить пользователя?')) return;
        try {
            await userService.delete(id);
            load();
        } catch (err: any) {
            alert(err.response?.data?.message || 'Ошибка');
        }
    };

    return (
        <div className="space-y-6">
            <div className="flex items-center justify-between">
                <h1 className="text-2xl font-bold text-gray-900">Пользователи</h1>
                <button
                    onClick={() => {
                        setEditingUser(null);
                        setShowForm(true);
                    }}
                    className="px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700"
                >
                    + Добавить
                </button>
            </div>

            {loading && <div className="text-center py-12 text-gray-500">Загрузка...</div>}

            {!loading && (
                <div className="bg-white rounded-lg shadow overflow-x-auto">
                    <table className="w-full">
                        <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ФИО</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Роль</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Кластер</th>
                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Действия</th>
                        </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-200">
                        {users.map((u) => (
                            <tr key={u.id} className="hover:bg-gray-50">
                                <td className="px-6 py-4 font-medium text-gray-900">{u.fio}</td>
                                <td className="px-6 py-4 text-sm text-gray-700">{u.email}</td>
                                <td className="px-6 py-4">
                    <span className="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded">
                      {u.role_label || u.role}
                    </span>
                                </td>
                                <td className="px-6 py-4 text-sm text-gray-700">{u.cluster_name || '—'}</td>
                                <td className="px-6 py-4 text-right space-x-2">
                                    <button
                                        onClick={() => {
                                            setEditingUser(u);
                                            setShowForm(true);
                                        }}
                                        className="px-3 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded"
                                    >
                                        Изменить
                                    </button>
                                    <button
                                        onClick={() => handleDelete(u.id)}
                                        className="px-3 py-1 text-xs bg-red-500 text-white hover:bg-red-600 rounded"
                                    >
                                        Удалить
                                    </button>
                                </td>
                            </tr>
                        ))}
                        </tbody>
                    </table>
                </div>
            )}

            {showForm && (
                <UserForm
                    user={editingUser}
                    onClose={() => setShowForm(false)}
                    onSaved={() => {
                        setShowForm(false);
                        load();
                    }}
                />
            )}
        </div>
    );
};

const UserForm: React.FC<{ user: User | null; onClose: () => void; onSaved: () => void }> = ({
                                                                                                 user,
                                                                                                 onClose,
                                                                                                 onSaved,
                                                                                             }) => {
    const [form, setForm] = useState({
        fio: user?.fio || '',
        email: user?.email || '',
        role: user?.role || ('tm' as UserRole),
        cluster_name: user?.cluster_name || '',
        azs_count: user?.azs_count || 0,
        password: '',
    });
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        setError('');
        try {
            if (user) {
                const data: any = { ...form };
                if (!data.password) delete data.password;
                await userService.update(user.id, data);
            } else {
                await userService.create(form);
            }
            onSaved();
        } catch (err: any) {
            setError(err.response?.data?.message || 'Ошибка');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div className="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div className="px-6 py-4 border-b flex items-center justify-between">
                    <h2 className="text-lg font-semibold">{user ? 'Редактировать' : 'Новый пользователь'}</h2>
                    <button onClick={onClose} className="text-gray-400">✕</button>
                </div>
                <form onSubmit={handleSubmit} className="p-6 space-y-4">
                    {error && <div className="p-3 bg-red-50 text-red-700 rounded text-sm">{error}</div>}

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">ФИО</label>
                        <input
                            type="text"
                            required
                            value={form.fio}
                            onChange={(e) => setForm({ ...form, fio: e.target.value })}
                            className="w-full px-3 py-2 border rounded"
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input
                            type="email"
                            required
                            value={form.email}
                            onChange={(e) => setForm({ ...form, email: e.target.value })}
                            className="w-full px-3 py-2 border rounded"
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Пароль</label>
                        <input
                            type="password"
                            required={!user}
                            value={form.password}
                            onChange={(e) => setForm({ ...form, password: e.target.value })}
                            placeholder={user ? 'Оставьте пустым, чтобы не менять' : ''}
                            className="w-full px-3 py-2 border rounded"
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Роль</label>
                        <select
                            value={form.role}
                            onChange={(e) => setForm({ ...form, role: e.target.value as UserRole })}
                            className="w-full px-3 py-2 border rounded"
                        >
                            <option value="tm">Территориальный менеджер</option>
                            <option value="expert">Эксперт</option>
                            <option value="coordinator">Координатор</option>
                        </select>
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Кластер</label>
                        <input
                            type="text"
                            value={form.cluster_name}
                            onChange={(e) => setForm({ ...form, cluster_name: e.target.value })}
                            className="w-full px-3 py-2 border rounded"
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Кол-во АЗС</label>
                        <input
                            type="number"
                            min="0"
                            value={form.azs_count}
                            onChange={(e) => setForm({ ...form, azs_count: Number(e.target.value) })}
                            className="w-full px-3 py-2 border rounded"
                        />
                    </div>

                    <div className="flex justify-end gap-2 pt-4">
                        <button
                            type="button"
                            onClick={onClose}
                            className="px-4 py-2 text-sm bg-gray-100 rounded hover:bg-gray-200"
                        >
                            Отмена
                        </button>
                        <button
                            type="submit"
                            disabled={loading}
                            className="px-4 py-2 text-sm bg-orange-600 text-white rounded hover:bg-orange-700"
                        >
                            {loading ? 'Сохранение...' : 'Сохранить'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};