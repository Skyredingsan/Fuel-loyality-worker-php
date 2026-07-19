import api from './api';
import type { KpiCategory, KpiIndicator } from '../types';

// Универсальная функция для безопасного извлечения данных
function extractData<T>(response: any, fallback: T[] = []): T[] {
    // Если ответ null или undefined
    if (!response || !response.data) {
        console.warn('API response is empty or invalid:', response);
        return fallback;
    }

    // Если response.data - массив
    if (Array.isArray(response.data)) {
        return response.data;
    }

    // Если response.data.data - массив (вложенная структура)
    if (response.data && Array.isArray(response.data.data)) {
        return response.data.data;
    }

    // Если сам response - массив (прямой ответ без обертки)
    if (Array.isArray(response)) {
        return response;
    }

    // Если ничего не подошло - возвращаем пустой массив
    console.warn('Unexpected data format:', response);
    return fallback;
}

export const kpiService = {
    async getCategories(): Promise<KpiCategory[]> {
        try {
            const response = await api.get<KpiCategory[] | { data: KpiCategory[] }>('/kpi/categories');
            console.log('Categories response:', response.data);
            return extractData<KpiCategory>(response);
        } catch (error) {
            console.error('Error fetching categories:', error);
            return [];
        }
    },

    async getIndicators(): Promise<KpiIndicator[]> {
        try {
            const response = await api.get<{ data: KpiIndicator[] } | KpiIndicator[]>('/kpi/indicators');
            console.log('Indicators response:', response.data);
            return extractData<KpiIndicator>(response);
        } catch (error) {
            console.error('Error fetching indicators:', error);
            return [];
        }
    },

    async getIndicatorsByCategory(categoryCode: string): Promise<KpiIndicator[]> {
        try {
            const response = await api.get<{ data: KpiIndicator[] } | KpiIndicator[]>(`/kpi/categories/${categoryCode}/indicators`);
            console.log(`Indicators for category ${categoryCode}:`, response.data);
            return extractData<KpiIndicator>(response);
        } catch (error) {
            console.error(`Error fetching indicators for category ${categoryCode}:`, error);
            return [];
        }
    },

    async create(data: Partial<KpiIndicator>): Promise<KpiIndicator | null> {
        try {
            const response = await api.post<KpiIndicator | { data: KpiIndicator }>('/kpi/indicators', data);
            console.log('Create response:', response.data);

            // Извлекаем созданный объект
            if (response.data && typeof response.data === 'object') {
                if ('data' in response.data && response.data.data) {
                    return response.data.data;
                }
                return response.data as KpiIndicator;
            }
            return null;
        } catch (error) {
            console.error('Error creating indicator:', error);
            throw error;
        }
    },

    async update(id: number, data: Partial<KpiIndicator>): Promise<KpiIndicator | null> {
        try {
            const response = await api.put<KpiIndicator | { data: KpiIndicator }>(`/kpi/indicators/${id}`, data);
            console.log('Update response:', response.data);

            if (response.data && typeof response.data === 'object') {
                if ('data' in response.data && response.data.data) {
                    return response.data.data;
                }
                return response.data as KpiIndicator;
            }
            return null;
        } catch (error) {
            console.error(`Error updating indicator ${id}:`, error);
            throw error;
        }
    },

    async delete(id: number): Promise<boolean> {
        try {
            await api.delete(`/kpi/indicators/${id}`);
            console.log(`Indicator ${id} deleted successfully`);
            return true;
        } catch (error) {
            console.error(`Error deleting indicator ${id}:`, error);
            return false;
        }
    },
};