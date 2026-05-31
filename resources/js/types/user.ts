export type UserRole = 'admin' | 'member';

export const USER_ROLES: ReadonlyArray<{ value: UserRole; label: string }> = [
    { value: 'member', label: 'Member' },
    { value: 'admin', label: 'Admin' },
];

export interface User {
    id: string;
    name: string;
    email: string;
    role: UserRole;
    createdAt: string | null;
    updatedAt: string | null;
}
