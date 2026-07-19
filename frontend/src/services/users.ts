import api from './api';
import type { User } from '../types';

export const userService = {
    async getAll(role?: string): Promise<User[]> {
        const url = role ? `/users?role=${role}` : '/users';
        const response = await api.get<{ data: User[] }>(url);
        return response.data.data;
    },

    async getTMs(): Promise<User[]> {
        const response = await api.get<{ data: User[] }>('/users/tms');
        return response.data.data;
    },

    async getById(id: number): Promise<User> {
        const response = await api.get<{ data: User }>(`/users/${id}`);
        return response.data.data;
    },

    async create(data: Partial<User> & { password: string }): Promise<User> {
        const response = await api.post<User>('/users/register', data);
        return response.data;
    },

    async update(id: number, data: Partial<User> & { password?: string }): Promise<User> {
        const response = await api.put<User>(`/users/${id}`, data);
        return response.data;
    },

    async delete(id: number): Promise<void> {
        await api.delete(`/users/${id}`);
    },
};