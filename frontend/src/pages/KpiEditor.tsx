import { useEffect, useState } from 'react';
import { kpiService } from '../services/kpi';
import type { KpiIndicator, KpiCategory } from '../types';

export const KpiEditor: React.FC = () => {
    const [indicators, setIndicators] = useState<KpiIndicator[]>([]);
    const [categories, setCategories] = useState<KpiCategory[]>([]);
    const [loading, setLoading] = useState(true);

    const load = () => {
        setLoading(true);
        Promise.all([kpiService.getIndicators(), kpiService.getCategories()])
            .then(([inds, cats]) => {
                setIndicators(inds);
                setCategories(cats);
            })
            .catch(console.error)
            .finally(() => setLoading(false));
    };

    useEffect(() => {
        load();
    }, []);

    const handleDelete = async (id: number) => {
        if (!confirm('Удалить показатель?')) return;
        try {
            await kpiService.delete(id);
            load();
        } catch (err: any) {
            alert(err.response?.data?.message || 'Ошибка');
        }
    };

    // Группировка по категориям
    const grouped = (categories || []).map((cat) => ({
        category: cat,
        indicators: (indicators || []).filter((i) => i.category_code === cat.code),
    }));

    return (
        <div className="space-y-6">
            <h1 className="text-2xl font-bold text-gray-900">KPI показатели</h1>

            {loading && <div className="text-center py-12 text-gray-500">Загрузка...</div>}

            {!loading &&
                grouped.map(({ category, indicators: inds }) => (
                    <div key={category.code} className="bg-white rounded-lg shadow">
                        <div className="px-6 py-4 border-b">
                            <h2 className="text-lg font-semibold">
                                {category.code} — {category.name}
                            </h2>
                            <p className="text-xs text-gray-500 mt-1">{category.description}</p>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-2 text-left text-xs">Код</th>
                                    <th className="px-4 py-2 text-left text-xs">Название</th>
                                    <th className="px-4 py-2 text-left text-xs">Тип</th>
                                    <th className="px-4 py-2 text-right text-xs">Порог</th>
                                    <th className="px-4 py-2 text-right text-xs">Вес</th>
                                    <th className="px-4 py-2 text-right text-xs">Действия</th>
                                </tr>
                                </thead>
                                <tbody className="divide-y">
                                {inds.map((ind) => (
                                    <tr key={ind.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-2 font-mono text-sm">{ind.code}</td>
                                        <td className="px-4 py-2 text-sm">{ind.name}</td>
                                        <td className="px-4 py-2">
                        <span
                            className={`px-2 py-0.5 text-xs rounded ${
                                ind.indicator_type === 'base'
                                    ? 'bg-blue-100 text-blue-700'
                                    : ind.indicator_type === 'extra'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-red-100 text-red-700'
                            }`}
                        >
                          {ind.indicator_type}
                        </span>
                                        </td>
                                        <td className="px-4 py-2 text-right text-sm">{ind.base_value ?? '—'}</td>
                                        <td className="px-4 py-2 text-right text-sm font-medium">
                                            {ind.base_weight ?? ind.extra_weight ?? ind.penalty_weight ?? '—'}
                                        </td>
                                        <td className="px-4 py-2 text-right">
                                            <button
                                                onClick={() => handleDelete(ind.id)}
                                                className="px-2 py-1 text-xs bg-red-500 text-white rounded hover:bg-red-600"
                                            >
                                                Удалить
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                ))}
        </div>
    );
};