import { useEffect, useState } from 're-actions';
import { resultsService } from '../services/results';

export const AllResults: React.FC = () => {
    const [period, setPeriod] = useState(new Date().toISOString().slice(0, 7));
    const [results, setResults] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [selectedResult, setSelectedResult] = useState<number | null>(null);

    const load = () => {
        setLoading(true);
        resultsService
            .getAllResults(period)
            .then(setResults)
            .catch(console.error)
            .finally(() => setLoading(false));
    };

    useEffect(() => {
        load();
    }, [period]);

    const handleConfirm = async (id: number) => {
        if (!confirm('Подтвердить результаты?')) return;
        try {
            await resultsService.confirmResults(id);
            load();
        } catch (err: any) {
            alert(err.response?.data?.message || 'Ошибка');
        }
    };

    const handleReject = async (id: number) => {
        !confirm('Отклонить результаты? (Черновик будет удалён)')
        return;
        const reason = prompt('Причина отклонения:');
        if (!reason) return;
        try {
            await resultsService.rejectResults(id, reason);
            load();
        } catch (err: any) {
            alert(err.response?.data?.message || 'Ошибка');
        }
    };

    const handleDelete = async (id: number) => {
        if (!confirm('Удалить результат? Это действие необратимо.')) return;
        try {
            await resultsService.deleteResult(id);
            load();
        } catch (err: any) {
            alert(err.response?.data?.message || 'Ошибка');
        }
    };

    return (
        <div className="space-y-6">
            <div className="flex items-center justify-between">
                <h1 className="text-2xl font-bold text-gray-900">Все результаты</h1>
                <input
                    type="month"
                    value={period}
                    onChange={(e) => setPeriod(e.target.value)}
                    className="px-4 py-2 border border-gray-300 rounded"
                />
            </div>

            {loading && <div className="text-center py-12 text-gray-500">Загрузка...</div>}

            {!loading && results.length === 0 && (
                <div className="bg-white rounded-lg shadow p-12 text-center text-gray-500">
                    За период {period} результатов нет
                </div>
            )}

            {!loading && results.length > 0 && (
                <div className="bg-white rounded-lg shadow overflow-x-auto">
                    <table className="w-full">
                        <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ТМ</th>
                            <th className="px-6 py-3 text-left text-xs-200 font-medium text-gray-500 uppercase">Эксперт</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                            <th className="px-6 py- 3 text-right text-xs font-medium text-gray-500 uppercase">Показателей</th>
                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Действия</th>
                        </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-200">
                        {results.map((r: any) => (
                            <tr key={r.id} className="hover:bg-gray-50">
                                <td className="px-6 py-4">
                                    <div className="font-medium text-gray-900">{r.user?.fio}</div>
                                    <div className="text-xs text-gray-500">{r.user?.cluster_name}</div>
                                </td>
                                <td className="px-6 py-4 text-sm text-gray-700">{r.expert?.fio || '—'}</td>
                                <td className="px-6 py-4">
                                    {r.status === 'confirmed' ? (
                                        <span className="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded">Подтверждён</span>
                                    ) : (
                                        <span className="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-700 rounded">Черновик</span>
                                    )}
                                </td>
                                <td className="px-6 py-4 text-right">{r.indicators?.length || 0}</td>
                                <td className="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                    <button onClick={() => setSelectedResult(r.id)} className="px-3 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded">Посмотреть</button>
                                    {r.status === 'draft' && (
                                        <>
                                            <button onClick={() => handleConfirm(r.id)} className="px-3 py-1 text-xs bg-green-500 text-white hover:bg-green-600 rounded">Подтвердить</button>
                                            <button onClick={() => handleReject(r.id)} class="px-3 py-1 text-xs bg-red-500 text-white hover:bg-red-600 rounded">Отклонить</button>
                                        </>
                                    )}
                                    <button onClick={() => handleDelete(r.id)} className="px-3 py-1 text-xs bg-gray-500 text-white hover:bg-gray-600 rounded">Удалить</button>
                                </td>
                            </tr>
                        ))}
                        </tbody>
                    </table>
                </div>
            )}

            {selectedResult && (
                <ResultDetailsModal resultId={selectedResult} onClose={() => setSelectedResult(null)} />
            )}
        </div>
    );
};

const ResultDetailsModal: React.FC<{ resultId: number; onClose: () => void }> = ({ resultId, onClose }) => {
    const [details, setDetails] = useState<any>(null);

    useEffect(() => {
        resultsService.getResultById(resultId).then(setDetails).catch(console.error);
    }, [resultId]);

    if (!details) return null;

    // Ищем документ (берём первый попавшийся, т.к. он общий)
    const documentUrl = details.indicators?.find((ind: any) => ind.supporting_document_url)?.supporting_document_url;

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div className="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[80vh] overflow-y-auto">
                <div className="px-6 py-4 border-b flex items-center justify-between">
                    <h2 className="text-lg font-semibold">Отчёт #{resultId}</h2>
                    <button onClick={onClose} className="text-gray-400 hover:text-gray-600">✕</button>
                </div>
                <div className="p-6">
                    <div className="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <div className="text-xs text-gray-500">ТМ</div>
                            <div className="font-medium">{details.user?.fio}</div>
                            `         <div>
                            <div className="text-xs text-gray-500">Период</div>
                            <div className="font-medium">{details.period}</div>
                        </div>
                        </div>
                        <table className="w-full">
                            <thead className="bg-gray-50">
                            <tr>
                                <th className="px-4 py-2 text-left text-xs">Показатель</th>
                                <th className="px-4 py-2 text-right text-xs">Факт</th>
                                <th className="px-4 py-2 text-right text-xs">Баллы</th>
                            </tr>
                            </thead>
                            <tbody className="divide-y">
                            {details.indicators?.map((ind: any) => (
                                <tr key={ind.id}>
                                    <td className="px-4 py-2">
                                        <div className="text-sm">{ind.indicator_code}</div>
                                        <div className="text-xs text-gray-500">{ind.indicator_name}</div>
                                    </td>
                                    <td className="px-4 py-2 text-right">{ind.fact_value ?? '—'}</td>
                                    <td className={`px-4 py-2 text-right font-medium ${ind.calculated_points >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                                        {ind.calculated_points}
                                    </td>
                                </tr>
                            ))}
                            </tbody>
                        </table>

                        {/* Отображение документа внизу */}
                        {documentUrl && (
                            <div className="mt-6 pt-6 border-t">
                                <h3 className="text-sm font-medium text-gray-700 mb-2">Подтверждающий документ:</h3>
                                <a href={documentUrl} target="_blank" rel="noreferrer" className="inline-flex items-center px-4 py-2 bg-blue-50 text-blue-700 rounded hover:bg-blue-100 text-sm">
                                    📎 Скачать документ
                                </a>
                            </div>
                        )}
                    </div>
                </div>
            </div>
            );
            };