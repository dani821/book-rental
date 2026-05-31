import { api } from '@/api/client';
import { type ResourceObject, type ResourceResponse } from '@/types/api';
import { type User, type UserRole } from '@/types/user';

export type UserResource = ResourceObject<
    'users',
    {
        name: string;
        email: string;
        role: UserRole;
        created_at: string | null;
        updated_at: string | null;
    }
>;

interface AuthResponse {
    data: UserResource;
    meta: { token: string };
}

export interface LoginPayload {
    email: string;
    password: string;
}

export interface RegisterPayload {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
}

export interface UpdatePasswordPayload {
    current_password: string;
    password: string;
    password_confirmation: string;
}

export function mapUserResource(resource: UserResource): User {
    return {
        id: resource.id,
        name: resource.attributes.name,
        email: resource.attributes.email,
        role: resource.attributes.role,
        createdAt: resource.attributes.created_at,
        updatedAt: resource.attributes.updated_at,
    };
}

export async function login(payload: LoginPayload): Promise<{ user: User; token: string }> {
    const { data } = await api.post<AuthResponse>('/login', payload);

    return { user: mapUserResource(data.data), token: data.meta.token };
}

export async function register(payload: RegisterPayload): Promise<{ user: User; token: string }> {
    const { data } = await api.post<AuthResponse>('/register', payload);

    return { user: mapUserResource(data.data), token: data.meta.token };
}

export async function logout(): Promise<void> {
    await api.post('/logout');
}

export async function me(): Promise<User> {
    const { data } = await api.get<ResourceResponse<UserResource>>('/me');

    return mapUserResource(data.data);
}

export async function updatePassword(userId: string, payload: UpdatePasswordPayload): Promise<void> {
    await api.put(`/users/${userId}/password`, payload);
}
