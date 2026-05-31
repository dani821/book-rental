import { storeToRefs } from 'pinia';

import { useAuthStore } from '@/stores/auth';

/**
 * Thin, component-facing wrapper over the auth store. Components consume this (and never
 * the API layer directly) so the store stays the single orchestration point for auth.
 */
export function useAuth() {
    const store = useAuthStore();
    const { user, isAuthenticated, isAdmin } = storeToRefs(store);

    return {
        user,
        isAuthenticated,
        isAdmin,
        login: store.login,
        register: store.register,
        logout: store.logout,
        updatePassword: store.updatePassword,
        initialize: store.initialize,
    };
}
