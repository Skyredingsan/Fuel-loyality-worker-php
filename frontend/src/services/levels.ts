import api from './api';
import type { Level } from '../types';

export const levelService = {
    async getAll(): Promise<Level[]> {
        const response = await api.get<{ data: Level[] }>('/levels');
        return response.data.data;
    },

    async getUserLevel(userId: number): Promise<Level> {
        const response = await api.get<{ data: Level }>(`/levels/user/${userId}`);
        return response.data.data;
    },

    async getUserHistory(userId: number): Promise<any[]> {
        const response = await api.get(`/levels/user/${userId}/history`);
        return response.data;
    },
};