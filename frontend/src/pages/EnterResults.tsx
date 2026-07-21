import { useEffect, useState } from 'react';
import { useAuth } from '../contexts/AuthContext';
import { userService } from '../services/users';
import { kpiService } from '../services/kpi';
import { resultsService } from '../services/results';
import { uploadService } from '../services/upload';
import type { User, KpiIndicator, EnterResultRequest, IndicatorResultInput } from '../types';

export const EnterResults: React.FC = () => {
    const { user } = useAuth();
    const [tms, setTms] = useState<User[]>([]);
    const [indicators, setIndicators] = useState<KpiIndicator[]>([]);
    const [selectedTm, setSelectedTm] = useState<number | ''>('');
    const [period, setPeriod] = useState(new Date().toISOString().slice(0, 7));
    const [values, setValues] = useState<Record<string, string>>({});
    const [documents, setDocuments] = useState<Record<string, string>>({});
    const [loading, setLoading] = useState(false);
    const [success, setSuccess] = useState('');
    const [error, setError] = useState('');

    useEffect(() => {
        Promise.all([userService.getTMs(), kpiService.getIndicators()])
            .then(([t, i]) => {
                setTms(t);
                setIndicators(i);
            })
            .catch(console.error);
    }, []);

    const handleFileUpload = async (file: File, indicatorCode: string) => {
        try {
            const url = await uploadService.uploadFile(file, 'indicator_result', indicatorCode);
            setDocuments({ ...documents, [indicatorCode]: url });
        } catch (err: any) {
            alert('Ошибка загрузки файла: ' + (err.response?.data?.message || err.message));
        }
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedTm || !user) return;

        setLoading(true);
        setError('');
        setSuccess('');

        try {
            const results: IndicatorResultInput[] = indicators
                .filter((ind) => values[ind.code] !== undefined && values[ind.code] !== '')
                .map((ind) => ({
                    indicator_code: ind.code,
                    fact_value: values[ind.code] ? parseFloat(values[ind.code]) : null,
                    document_url: documents[ind.code] || null,
                }));

            if (results.length === 0) {
                throw new Error('Заполните хотя бы один показатель');
            }

            const payload: EnterResultRequest = {
                user_id: Number(selectedTm),
                period,
                results,
            };

            await resultsService.enterResults(payload);
            setSuccess('Результаты успешно сохранены!');
            setValues({});
            setDocuments({});
        } catch (err: any) {
            setError(err.response?.data?.message || err.message || 'Ошибка сохранения');
        } finally {
            setLoading(false);
        }
    };

    const grouped = indicators.reduce((acc, ind) => {
        const code = ind.category_code || 'OTHER';
        if (!acc[code]) acc[code] = [];
        acc[code].push(ind);
        return acc;
    }, {} as Record<string, KpiIndicator[]>);

    return (
        <div className="space-y-6">
            <h1 className="text-2xl font-bold text-gray-900">Ввод результатов</h1>

            {success && (
                <div className="p-4 bg-green-50 border border-green-200 text-green-700 rounded">
                    {success}
                </div>
            )}
            {error && (
                <div className="p-4 bg-red-50 border border-red-200 text-red-700 rounded">{error}</div>
            )}

            <form onSubmit={handleSubmit} className="bg-white rounded-lg shadow p-6 space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Территориальный менеджер
                        </label>
                        <select
                            value={selectedTm}
                            onChange={(e) => setSelectedTm(e.target.value ? Number(e.target.value) : '')}
                            required
                            className="w-full px-4 py-2 border border-gray-300 rounded"
                        >
                            <option value="">Выберите ТМ</option>
                            {tms.map((tm) => (
                                <option key={tm.id} value={tm.id}>
                                    {tm.fio} ({tm.cluster_name || '—'})
                                </option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Период</label>
                        <input
                            type="month"
                            value={period}
                            onChange={(e) => setPeriod(e.target.value)}
                            required
                            className="w-full px-4 py-2 border border-gray-300 rounded"
                        />
                    </div>
                </div>

                {Object.entries(grouped).map(([catCode, inds]) => (
                    <div key={catCode} className="border rounded p-4">
                        <h3 className="font-semibold text-gray-900 mb-3">
                            {inds[0]?.category_name || catCode} ({catCode})
                        </h3>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {inds.map((ind) => (
                                <div key={ind.code} className="border-b pb-4">
                                    <label className="block text-sm text-gray-700 mb-1">
                                        {ind.code} — {ind.name}
                                        <span className="text-xs text-gray-500 ml-1">({ind.unit})</span>
                                    </label>
                                    <div className="flex gap-2">
                                        <input
                                            type="number"
                                            step="0.01"
                                            value={values[ind.code] || ''}
                                            onChange={(e) =>
                                                setValues({ ...values, [ind.code]: e.target.value })
                                            }
                                            placeholder="Фактическое значение"
                                            className="flex-1 px-3 py-2 border border-gray-300 rounded text-sm"
                                        />
                                        <input
                                            type="file"
                                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                                            onChange={(e) => {
                                                const file = e.target.files?.[0];
                                                if (file) {
                                                    handleFileUpload(file, ind.code);
                                                }
                                            }}
                                            className="text-xs file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-gray-100 hover:file:bg-gray-200"
                                        />
                                    </div>
                                    {documents[ind.code] && (
                                        <div className="text-xs text-green-600 mt-1">
                                            ✓ Файл загружен
                                        </div>
                                    )}
                                </div>
                            ))}
                        </div>
                    </div>
                ))}

                <div className="flex justify-end">
                    <button
                        type="submit"
                        disabled={loading || !selectedTm}
                        className="px-6 py-2.5 bg-orange-600 text-white rounded font-medium hover:bg-orange-700 disabled:opacity-50"
                    >
                        {loading ? 'Сохранение...' : 'Сохранить результаты'}
                    </button>
                </div>
            </form>
        </div>
    );
};