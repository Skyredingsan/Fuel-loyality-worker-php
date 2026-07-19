import api from './api';
import type { CsvExport } from '../types';

export const reportService = {
    async startExport(period: string): Promise<{ export_id: string; status: string }> {
        const response = await api.post('/reports/export', { period });
        return response.data;
    },

    async getExportStatus(id: string): Promise<CsvExport> {
        const response = await api.get<CsvExport>(`/reports/exports/${id}`);
        return response.data;
    },

    getDownloadUrl(id: string): string {
        return `/api/reports/exports/${id}/download`;
    },
};