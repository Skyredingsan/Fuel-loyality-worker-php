import { useState } from 'react';
import { reportService } from '../services/reports';
import type { CsvExport } from '../types';

export const Reports: React.FC = () => {
    const [period, setPeriod] = useState(new Date().toISOString().slice(0, 7));
    const [exporting, setExporting] = useState(false);
    const [exportInfo, setExportInfo] = useState<CsvExport | null>(null);
    const [error, setError] = useState('');

    const handleExport = async () => {
        setExporting(true);
        setError('');
        setExportInfo(null);

        try {
            const { export_id } = await reportService.startExport(period);

            // Поллим статус
            const poll = async () => {
                const status = await reportService.getExportStatus(export_id);
                setExportInfo(status);

                if (status.status === 'pending' || status.status === 'processing') {
                    setTimeout(poll, 1000);
                }
            };

            poll();
        } catch (err: any) {
            setError(err.response?.data?.message || 'Ошибка экспорта');
        } finally {
            setExporting(false);
        }
    };

    const handleDownload = () => {
        if (!exportInfo?.id) return;
        const token = localStorage.getItem('token');
        const url = `/api/reports/exports/${exportInfo.id}/download?token=${token}`;

        // Открываем в новой вкладке с заголовком Authorization через fetch
        fetch(`/api/reports/exports/${exportInfo.id}/download`, {
            headers: { Authorization: `Bearer ${token}` },
        })
            .then((r) => r.blob())
            .then((blob) => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `export_${period}.csv`;
                a.click();
                window.URL.revokeObjectURL(url);
            });
    };

    return (
        <div className="space-y-6">
            <h1 className="text-2xl font-bold text-gray-900">Экспорт результатов в CSV</h1>

            <div className="bg-white rounded-lg shadow p-6">
                <div className="flex items-end gap-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Период</label>
                        <input
                            type="month"
                            value={period}
                            onChange={(e) => setPeriod(e.target.value)}
                            className="px-4 py-2 border border-gray-300 rounded"
                        />
                    </div>
                    <button
                        onClick={handleExport}
                        disabled={exporting}
                        className="px-6 py-2 bg-orange-600 text-white rounded hover:bg-orange-700 disabled:opacity-50"
                    >
                        {exporting ? 'Запуск...' : 'Сформировать CSV'}
                    </button>
                </div>

                {error && (
                    <div className="mt-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded text-sm">
                        {error}
                    </div>
                )}

                {exportInfo && (
                    <div className="mt-6 p-4 bg-gray-50 rounded">
                        <div className="flex items-center justify-between">
                            <div>
                                <div className="text-sm text-gray-500">Статус экспорта</div>
                                <div className="font-medium">
                                    {exportInfo.status === 'ready' && '✅ Готов к скачиванию'}
                                    {exportInfo.status === 'pending' && '⏳ В очереди...'}
                                    {exportInfo.status === 'processing' && '⚙️ Генерация...'}
                                    {exportInfo.status === 'failed' && '❌ Ошибка: ' + exportInfo.error}
                                </div>
                                {exportInfo.rows_count > 0 && (
                                    <div className="text-xs text-gray-500 mt-1">
                                        Строк: {exportInfo.rows_count}
                                    </div>
                                )}
                            </div>
                            {exportInfo.status === 'ready' && (
                                <button
                                    onClick={handleDownload}
                                    className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                                >
                                    ⬇ Скачать CSV
                                </button>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};