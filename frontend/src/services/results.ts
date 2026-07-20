import api from './api';
import type { EnterResultRequest, FullResultSummary } from '../types';

export const resultsService = {
    async enterResults(data: EnterResultRequest): Promise<any> {
        const response = await api.post('/results/enter', data);
        return response.data;
    },

    async confirmResults(id: number): Promise<void> {
        await api.post(`/results/${id}/confirm`);
    },

    async rejectResults(id: number, reason: string): Promise<void> {
        await api.post(`/results/${id}/reject`, { reason });
    },

    async getResultById(id: number): Promise<any> {
        const response = await api.get(`/results/${id}`);
        return response.data;
    },

    async updateResults(id: number, data: EnterResultRequest): Promise<any> {
        const response = await api.put(`/results/${id}`, data);
        return response.data;
    },

    async getMyResults(period?: string): Promise<FullResultSummary> {
        const url = period ? `/results/my?period=${period}` : '/results/my';
        const response = await api.get<{ data: FullResultSummary }>(url);
        return response.data.data;
    },

    async getUserResults(userId: number, period: string): Promise<FullResultSummary> {
        const response = await api.get<{ data: FullResultSummary }>(`/results/user/${userId}?period=${period}`);
        return response.data.data;
    },

    async getAllResults(period: string): Promise<any[]> {
        const response = await api.get<any[]>(`/results?period=${period}`);
        return response.data;
    },

    async getYearlySummary(userId: number, year: number): Promise<any> {
        const response = await api.get(`/results/user/${userId}/yearly?year=${year}`);
        return response.data;
    },

    async deleteResult(id: number): Promise<void> {
        await api.delete(`/results/${id}`);
    },
};
