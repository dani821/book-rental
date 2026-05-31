import { computed, ref } from 'vue';
import { defineStore } from 'pinia';

import * as authApi from '@/api/auth';
import { type LoginPayload, type RegisterPayload, type UpdatePasswordPayload } from '@/api/auth';
import { clearToken, getToken, setToken } from '@/api/token';
import { type User } from '@/types/user';

export const useAuthStore = defineStore('auth', () => {
    const user = ref<User | null>(null);
    const token = ref<string | null>(getToken());
    const initialized = ref(false);

    const isAuthenticated = computed(() => token.value !== null && user.value !== null);
    const isAdmin = computed(() => user.value?.role === 'admin');

    function setSession(nextUser: User, nextToken: string): void {
        user.value = nextUser;
        token.value = nextToken;
        setToken(nextToken);
    }

    function reset(): void {
        user.value = null;
        token.value = null;
        clearToken();
    }

    async function login(payload: LoginPayload): Promise<void> {
        const { user: nextUser, token: nextToken } = await authApi.login(payload);
        setSession(nextUser, nextToken);
    }

    async function register(payload: RegisterPayload): Promise<void> {
        const { user: nextUser, token: nextToken } = await authApi.register(payload);
        setSession(nextUser, nextToken);
    }

    async function fetchMe(): Promise<User> {
        const nextUser = await authApi.me();
        user.value = nextUser;

        return nextUser;
    }

    async function logout(): Promise<void> {
        try {
            await authApi.logout();
        } finally {
            // Always clear local state, even if the network call fails.
            reset();
        }
    }

    async function updatePassword(payload: UpdatePasswordPayload): Promise<void> {
        if (!user.value) {
            throw new Error('Cannot update password without an authenticated user.');
        }

        await authApi.updatePassword(user.value.id, payload);
    }

    /**
     * Rehydrate the session on app boot. If a token is persisted, fetch the current user;
     * a failure (e.g. an expired token) clears local state. Runs at most once.
     */
    async function initialize(): Promise<void> {
        if (initialized.value) {
            return;
        }

        if (token.value) {
            try {
                await fetchMe();
            } catch {
                reset();
            }
        }

        initialized.value = true;
    }

    return {
        user,
        token,
        initialized,
        isAuthenticated,
        isAdmin,
        login,
        register,
        logout,
        fetchMe,
        updatePassword,
        initialize,
        reset,
    };
});
