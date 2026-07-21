import { useEffect, useState } from 'react';
import { useAuth } from '../contexts/AuthContext';
import { resultsService } from '../services/results';
import api from '../services/api';
import type { FullResultSummary } from '../types';

export const Results: React.FC = () => {
    const { user } = useAuth();
    const [period, setPeriod] = useState(new Date().toISOString().slice(0, 7));
    const [summary, setSummary] = useState<FullResultSummary | null>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!user) return;
        setLoading(true);
        resultsService
            .getMyResults(period)
            .then(setSummary)
            .catch(console.error)
            .finally(() => setLoading(false));
    }, [user, period]);

    return (
        <div className="space-y-6">
            <div className="flex items-center justify-between">
                <h1 className="text-2xl font-bold text-gray-900">Мои результаты</h1>
                <input
                    type="month"
                    value={period}
                    onChange={(e) => setPeriod(e.target.value)}
                    className="px-4 py-2 border border-gray-300 rounded"
                />
            </div>

            {loading && <div className="text-center py-12 text-gray-500">Загрузка...</div>}

            {!loading && summary && (
                <>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div className="bg-white rounded-lg shadow p-6">
                            <div className="text-sm text-gray-500">Баллы за период</div>
                            <div className="text-3xl font-bold text-gray-900 mt-2">{summary.total_points}</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-6">
                            <div className="text-sm text-gray-500">Годовой баланс</div>
                            <div className="text-3xl font-bold text-green-600 mt-2">{summary.yearly_points}</div>
                        </div>
                        <div className="bg-white rounded-lg shadow p-6">
                            <div className="text-sm text-gray-500">Уровень</div>
                            <div className="text-xl font-bold text-orange-600 mt-2">{summary.level?.name || '—'}</div>
                            {summary.level?.privileges?.bonus && (
                                <div className="text-xs text-gray-500 mt-1">{summary.level.privileges.bonus}</div>
                            )}
                        </div>
                    </div>

                    <div className="bg-white rounded-lg shadow">
                        <div className="px-6 py-4 border-b">
                            <h2 className="text-lg font-semibold">Сводка по категориям</h2>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Категория</th>
                                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">База</th>
                                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Бонусы</th>
                                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Штрафы</th>
                                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Итого</th>
                                </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                {summary.categories.map((cat) => (
                                    <tr key={cat.category_code} className="hover:bg-gray-50">
                                        <td className="px-6 py-4">
                                            <div className="font-medium text-gray-900">{cat.category_code}</div>
                                            <div className="text-xs text-gray-500">{cat.category_name}</div>
                                        </td>
                                        <td className="px-6 py-4 text-right">{cat.base_points}</td>
                                        <td className="px-6 py-4 text-right text-green-600">+{cat.extra_points}</td>
                                        <td className="px-6 py-4 text-right text-red-600">{cat.penalty_points}</td>
                                        <td className="px-6 py-4 text-right font-bold">{cat.total_points}</td>
                                    </tr>
                                ))}
                                </tbody>
                                <tfoot className="bg-gray-50">
                                <tr>
                                    <td className="px-6 py-4 font-bold">Итого за период</td>
                                    <td colSpan={3}></td>
                                    <td className="px-6 py-4 text-right font-bold text-orange-600 text-lg">{summary.total_points}</td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {summary.detailed_results && summary.detailed_results.length > 0 && (
                        <div className="bg-white rounded-lg shadow">
                            <div className="px-6 py-4 border-b">
                                <h2 className="text-lg font-semibold">Детальные результаты</h2>
                            </div>
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Показатель</th>
                                        <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Факт</th>
                                        <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Баллы</th>
                                        <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Документ</th>
                                    </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-200">
                                    {summary.detailed_results.map((r: any) => (
                                        <tr key={r.id} className="hover:bg-gray-50">
                                            <td className="px-6 py-4">
                                                <div className="font-medium text-gray-900">{r.indicator_code}</div>
                                                <div className="text-xs text-gray-500">{r.indicator_name}</div>
                                            </td>
                                            <td className="px-6 py-4 text-right">{r.fact_value ?? '—'}</td>
                                            <td className={`px-6 py-4 text-right font-medium ${r.calculated_points >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                                                {r.calculated_points}
                                            </td>
                                            <td className="px-6 py-4 text-center">
                                                {r.supporting_document_url ? (
                                                    <a href={r.supporting_document_url} target="_blank" rel="noreferrer" className="text-blue-500 hover:underline text-xs">Открыть</a>
                                                ) : '—'}
                                            </td>
                                        </tr>
                                    ))}
                                    </tbody>
                                </table>
                                {/* Отображение документа внизу */}
                                {(() => {
                                    const docUrl = summary.detailed_results?.find((r: any) => r.supporting_document_url)?.supporting_document_url;
                                    if (!docUrl) return null;
                                    return (
                                        <div className="mt-6 pt-6 border-t">
                                            <h3 className="text-sm font-medium text-gray-700 mb-2">Подтверждающий документ:</h3>
                                            <button
                                                onClick={async () => {
                                                    try {
                                                        const response = await api.get(docUrl, { responseType: 'blob' });
                                                        const fileUrl = window.URL.createObjectURL(new Blob([response.data]));
                                                        const link = document.createElement('a');
                                                        link.href = fileUrl;
                                                        link.setAttribute('download', docUrl.split('/').pop() || 'document');
                                                        document.body.appendChild(link);
                                                        link.click();
                                                        link.parentNode?.removeChild(link);
                                                    } catch (err) {
                                                        alert('Ошибка скачивания файла');
                                                    }
                                                }}
                                                className="inline-flex items-center px-4 py-2 bg-blue-50 text-blue-700 rounded hover:bg-blue-100 text-sm"
                                            >
                                                📎 Скачать документ
                                            </button>
                                        </div>
                                    );
                                })()}
                            </div>
                        </div>
                    )}
                </>
            )}
        </div>
    );
};