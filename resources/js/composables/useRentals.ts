import { ref } from 'vue';

import * as rentalsApi from '@/api/rentals';
import { isAppError, type PaginationMeta } from '@/types/api';
import { type Rental } from '@/types/rental';

const EMPTY_META: PaginationMeta = {
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
    from: null,
    to: null,
};

/**
 * The signed-in user's rentals: a paginated list plus the extend / log-progress / finish
 * operations. Each mutation returns the fresh rental and patches it into the list so the UI
 * updates without a full reload.
 */
export function useRentals() {
    const items = ref<Rental[]>([]);
    const meta = ref<PaginationMeta>({ ...EMPTY_META });
    const loading = ref(false);
    const error = ref('');
    const page = ref(1);

    async function fetch(): Promise<void> {
        loading.value = true;
        error.value = '';

        try {
            const result = await rentalsApi.listRentals(page.value);
            items.value = result.items;
            meta.value = result.meta;
        } catch (caught) {
            error.value = isAppError(caught) ? caught.message : 'Failed to load your rentals.';
            items.value = [];
        } finally {
            loading.value = false;
        }
    }

    function goToPage(nextPage: number): void {
        page.value = nextPage;
        void fetch();
    }

    function replaceRental(updated: Rental): void {
        const index = items.value.findIndex((rental) => rental.id === updated.id);
        if (index !== -1) {
            items.value[index] = updated;
        }
    }

    async function extend(id: string): Promise<void> {
        replaceRental(await rentalsApi.extendRental(id));
    }

    async function updateProgress(id: string, currentPage: number): Promise<void> {
        replaceRental(await rentalsApi.updateReadingProgress(id, currentPage));
    }

    async function finish(id: string): Promise<void> {
        replaceRental(await rentalsApi.finishRental(id));
    }

    return {
        items,
        meta,
        loading,
        error,
        page,
        fetch,
        goToPage,
        extend,
        updateProgress,
        finish,
    };
}
