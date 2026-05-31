import { ref } from 'vue';

import * as usersApi from '@/api/users';
import { type CreateUserPayload, type ResetPasswordPayload } from '@/api/users';
import { isAppError, type PaginationMeta } from '@/types/api';
import { type User } from '@/types/user';

const EMPTY_META: PaginationMeta = {
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
    from: null,
    to: null,
};

/**
 * Admin user-management state: a paginated list (no filters) plus the create / delete /
 * reset-password operations. Views call these; the API parsing lives in `api/users.ts`.
 */
export function useUsers() {
    const items = ref<User[]>([]);
    const meta = ref<PaginationMeta>({ ...EMPTY_META });
    const loading = ref(false);
    const error = ref('');
    const page = ref(1);

    async function fetch(): Promise<void> {
        loading.value = true;
        error.value = '';

        try {
            const result = await usersApi.listUsers(page.value);
            items.value = result.items;
            meta.value = result.meta;
        } catch (caught) {
            error.value = isAppError(caught) ? caught.message : 'Failed to load users.';
            items.value = [];
        } finally {
            loading.value = false;
        }
    }

    function goToPage(nextPage: number): void {
        page.value = nextPage;
        void fetch();
    }

    async function create(payload: CreateUserPayload): Promise<User> {
        return usersApi.createUser(payload);
    }

    async function remove(id: string): Promise<void> {
        await usersApi.deleteUser(id);
    }

    async function resetPassword(id: string, payload: ResetPasswordPayload): Promise<void> {
        await usersApi.resetUserPassword(id, payload);
    }

    return {
        items,
        meta,
        loading,
        error,
        page,
        fetch,
        goToPage,
        create,
        remove,
        resetPassword,
    };
}
