import { mapUserResource, type UserResource } from '@/api/auth';
import { api } from '@/api/client';
import { mapPaginationMeta, type Paginated, type ResourceResponse } from '@/types/api';
import { type User, type UserRole } from '@/types/user';

export interface CreateUserPayload {
    name: string;
    email: string;
    password: string;
    role: UserRole;
}

export interface ResetPasswordPayload {
    password: string;
    password_confirmation: string;
}

interface PaginatedUsersResponse {
    data: UserResource[];
    meta: Parameters<typeof mapPaginationMeta>[0];
}

export async function listUsers(page = 1): Promise<Paginated<User>> {
    const { data } = await api.get<PaginatedUsersResponse>('/users', { params: { page } });

    return { items: data.data.map(mapUserResource), meta: mapPaginationMeta(data.meta) };
}

export async function createUser(payload: CreateUserPayload): Promise<User> {
    const { data } = await api.post<ResourceResponse<UserResource>>('/users', payload);

    return mapUserResource(data.data);
}

export async function deleteUser(id: string): Promise<void> {
    await api.delete(`/users/${id}`);
}

/** Admin password reset for another user (no current_password required by the API). */
export async function resetUserPassword(id: string, payload: ResetPasswordPayload): Promise<void> {
    await api.put(`/users/${id}/password`, payload);
}
