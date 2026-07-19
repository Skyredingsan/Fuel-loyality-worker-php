import api from './api';

export const uploadService = {
    async uploadFile(file: File, type: string, entityId?: string): Promise<string> {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('type', type);
        if (entityId) formData.append('entity_id', entityId);

        const response = await api.post<{ url: string }>('/upload', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        return response.data.url;
    },
};