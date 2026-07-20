import { useEffect, useState } from 'react';
import { useAuth } from '../contexts/AuthContext';
import { userService } from '../services/users';
import { resultsService } from '../services/results';
import { levelService } from '../services/levels';
import type { User, FullResultSummary } from '../types';

export const Dashboard: React.FC = () => {
  const { user } = useAuth();
  const [tms, setTms] = useState<User[]>([]);
  const [summaries, setSummaries] = useState<Record<number, FullResultSummary>>({});
  const [loading, setLoading] = useState(true);
  const currentPeriod = new Date().toISOString().slice(0, 7);

  useEffect(() => {
    if (!user) return;

    if (user.role === 'tm') {
      // Для ТМ — свои результаты
      Promise.all([
        resultsService.getMyResults(currentPeriod),
        levelService.getUserLevel(user.id),
      ])
        .then(([s]) => {
          setSummaries({ [user.id]: s });
        })
        .catch(console.error)
        .finally(() => setLoading(false));
    } else {
      // Для координатора/эксперта — список всех ТМ
      userService.getTMs().then(async (tmList) => {
        setTms(tmList);
        const results: Record<number, FullResultSummary> = {};
        for (const tm of tmList) {
          try {
            const summary = await resultsService.getUserResults(tm.id, currentPeriod);
            results[tm.id] = summary;
          } catch {
            // Если нет результатов — пропускаем
          }
        }
        setSummaries(results);
        setLoading(false);
      });
    }
  }, [user, currentPeriod]);

  if (loading) return <div className="text-center py-12 text-gray-500">Загрузка...</div>;

  if (user?.role === 'tm') {
    // Личный дашборд ТМ
    const summary = summaries[user.id];
    return (
      <div className="space-y-6">
        <h1 className="text-2xl font-bold text-gray-900">Здравствуйте, {user.fio}! 👋</h1>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div className="bg-white rounded-lg shadow p-6">
            <div className="text-sm text-gray-500">Баллы за {currentPeriod}</div>
            <div className="text-2xl font-bold text-gray-900 mt-1">{summary?.total_points || 0}</div>
          </div>
          <div className="bg-white rounded-lg shadow p-6">
            <div className="text-sm text-gray-500">Годовой баланс</div>
            <div className="text-2xl font-bold text-green-600 mt-1">{summary?.yearly_points || 0}</div>
          </div>
          <div className="bg-white rounded-lg shadow p-6">
            <div className="text-sm text-gray-500">Уровень</div>
            <div className="text-xl font-bold text-orange-600 mt-1">{summary?.level?.name || '—'}</div>
          </div>
        </div>
        {summary && summary.categories && summary.categories.length > 0 && (
          <div className="bg-white rounded-lg shadow">
            <div className="px-6 py-4 border-b">
              <h2 className="text-lg font-semibold">Баллы по категориям за {currentPeriod}</h2>
            </div>
            <div className="p-6 space-y-3">
              {summary.categories.map((cat) => (
                <div key={cat.category_code} className="flex items-center justify-between p-4 bg-gray-50 rounded">
                  <div>
                    <div className="font-medium text-gray-900">{cat.category_code} — {cat.category_name}</div>
                    <div className="text-xs text-gray-500 mt-1">
                      База: {cat.base_points} • Бонусы: {cat.extra_points} • Штрафы: {cat.penalty_points}
                    </div>
                  </div>
                  <div className={`text-2xl font-bold ${cat.total_points >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                    {cat.total_points}
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
    );
  }

  // Дашборд координатора/эксперта — список ТМ
  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold text-gray-900">Сводка по ТМ за {currentPeriod}</h1>
      <div className="bg-white rounded-lg shadow overflow-x-auto">
        <table className="w-full">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ТМ</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Кластер</th>
              <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Баллы за месяц</th>
              <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Годовой баланс</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Уровень</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-200">
            {tms.map((tm) => {
              const summary = summaries[tm.id];
              return (
                <tr key={tm.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 font-medium text-gray-900">{tm.fio}</td>
                  <td className="px-6 py-4 text-sm text-gray-700">{tm.cluster_name || '—'}</td>
                  <td className="px-6 py-4 text-right font-medium">{summary?.total_points || 0}</td>
                  <td className="px-6 py-4 text-right font-medium text-green-600">{summary?.yearly_points || 0}</td>
                  <td className="px-6 py-4">
                    <span className="px-2 py-1 text-xs bg-orange-100 text-orange-700 rounded">
                      {summary?.level?.name || '—'}
                    </span>
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>
    </div>
  );
};
