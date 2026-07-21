import { useEffect, useState } from 'react';
import { useAuth } from '../contexts/AuthContext';
import { resultsService } from '../services/results';
import { levelService } from '../services/levels';
import type { FullResultSummary, Level } from '../types';

export const Dashboard: React.FC = () => {
  const { user } = useAuth();
  const [summary, setSummary] = useState<FullResultSummary | null>(null);
  const [level, setLevel] = useState<Level | null>(null);
  const [allResults, setAllResults] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const currentPeriod = new Date().toISOString().slice(0, 7);

  useEffect(() => {
    if (!user) return;

    if (user.role === 'tm') {
      // Для ТМ — свои результаты (быстро)
      Promise.all([
        resultsService.getMyResults(currentPeriod),
        levelService.getUserLevel(user.id),
      ])
          .then(([s, l]) => {
            setSummary(s);
            setLevel(l);
          })
          .catch(console.error)
          .finally(() => setLoading(false));
    } else {
      // Для координатора — один запрос на все результаты за месяц
      resultsService.getAllResults(currentPeriod)
          .then((res) => {
            setAllResults(res);
          })
          .catch(console.error)
          .finally(() => setLoading(false));
    }
  }, [user, currentPeriod]);

  if (loading) return <div className="text-center py-12 text-gray-500">Загрузка...</div>;

  // Личный дашборд ТМ
  if (user?.role === 'tm') {
    return (
        <div className="space-y-6">
          <h1 className="text-2xl font-bold text-gray-900">Здравствуйте, {user.fio}! 👋</h1>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div className="bg-white rounded-lg shadow p-6">
              <div className="text-sm text-gray-500">Текущий уровень</div>
              <div className="text-2xl font-bold text-orange-600 mt-1">{level?.name || '—'}</div>
              {level?.privileges?.bonus && (
                  <div className="text-xs text-gray-500 mt-2">{level.privileges.bonus}</div>
              )}
            </div>
            <div className="bg-white rounded-lg shadow p-6">
              <div className="text-sm text-gray-500">Баллы за {currentPeriod}</div>
              <div className="text-2xl font-bold text-gray-900 mt-1">{summary?.total_points || 0}</div>
            </div>
            <div className="bg-white rounded-lg shadow p-6">
              <div className="text-sm text-gray-500">Годовой баланс</div>
              <div className="text-2xl font-bold text-green-600 mt-1">{summary?.yearly_points || 0}</div>
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

  // Дашборд координатора/эксперта — список ТМ (один запрос)
  // Группируем результаты по ТМ и считаем сумму баллов
  const tmSummaries = allResults.reduce((acc, r) => {
    const tmId = r.user?.id;
    if (!tmId) return acc;
    if (!acc[tmId]) {
      acc[tmId] = {
        fio: r.user?.fio,
        cluster_name: r.user?.cluster_name,
        total_points: 0,
        status: r.status,
      };
    }
    // Суммируем баллы по показателям
    r.indicators?.forEach((ind: any) => {
      acc[tmId].total_points += ind.calculated_points || 0;
    });
    return acc;
  }, {} as Record<number, any>);

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
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
            </tr>
            </thead>
            <tbody className="divide-y divide-gray-200">
            {Object.values(tmSummaries).map((tm: any) => (
                <tr key={tm.fio} className="hover:bg-gray-50">
                  <td className="px-6 py-4 font-medium text-gray-900">{tm.fio}</td>
                  <td className="px-6 py-4 text-sm text-gray-700">{tm.cluster_name || '—'}</td>
                  <td className="px-6 py-4 text-right font-medium">{tm.total_points}</td>
                  <td className="px-6 py-4">
                    {tm.status === 'confirmed' ? (
                        <span className="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">Подтверждён</span>
                    ) : (
                        <span className="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded">Черновик</span>
                    )}
                  </td>
                </tr>
            ))}
            {Object.keys(tmSummaries).length === 0 && (
                <tr>
                  <td colSpan={4} className="px-6 py-12 text-center text-gray-500">
                    За период {currentPeriod} данных нет
                  </td>
                </tr>
            )}
            </tbody>
          </table>
        </div>
      </div>
  );
};