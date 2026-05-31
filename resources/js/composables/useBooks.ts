import { reactive, ref } from 'vue';

import * as booksApi from '@/api/books';
import { isAppError, type PaginationMeta } from '@/types/api';
import { type Book, type BookFormPayload, type BookListParams, type BookSearchField, type BookGenre } from '@/types/book';

const SEARCH_DEBOUNCE_MS = 350;

const EMPTY_META: PaginationMeta = {
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
    from: null,
    to: null,
};

function defaultParams(): BookListParams {
    return {
        page: 1,
        search: '',
        searchField: 'title',
        genre: null,
        availableOnly: false,
        sort: 'title',
    };
}

/**
 * Single source of truth for the Books list: data, pagination, filters/sort/search state,
 * and the read/write operations. The member and admin views both consume this; presentation
 * (cards vs table) and which actions are exposed live in the views, not here.
 */
export function useBooks() {
    const items = ref<Book[]>([]);
    const meta = ref<PaginationMeta>({ ...EMPTY_META });
    const loading = ref(false);
    const error = ref('');

    const params = reactive<BookListParams>(defaultParams());

    // Single-book (detail view) state, kept here so the one composable is the only data path.
    const current = ref<Book | null>(null);
    const currentLoading = ref(false);
    const currentError = ref('');

    let searchTimer: ReturnType<typeof setTimeout> | undefined;

    async function fetch(): Promise<void> {
        loading.value = true;
        error.value = '';

        try {
            const result = await booksApi.listBooks(params);
            items.value = result.items;
            meta.value = result.meta;
        } catch (caught) {
            error.value = isAppError(caught) ? caught.message : 'Failed to load books.';
            items.value = [];
        } finally {
            loading.value = false;
        }
    }

    function debouncedFetch(): void {
        if (searchTimer) {
            clearTimeout(searchTimer);
        }
        searchTimer = setTimeout(() => void fetch(), SEARCH_DEBOUNCE_MS);
    }

    function setSearch(value: string): void {
        params.search = value;
        params.page = 1;
        debouncedFetch();
    }

    function setSearchField(field: BookSearchField): void {
        params.searchField = field;
        params.page = 1;
        if (params.search.trim() !== '') {
            void fetch();
        }
    }

    function setGenre(genre: BookGenre | null): void {
        params.genre = genre;
        params.page = 1;
        void fetch();
    }

    function setAvailableOnly(value: boolean): void {
        params.availableOnly = value;
        params.page = 1;
        void fetch();
    }

    function setSort(sort: string): void {
        params.sort = sort;
        params.page = 1;
        void fetch();
    }

    function goToPage(page: number): void {
        params.page = page;
        void fetch();
    }

    // --- Local state helpers so single mutations reflect without a full reload ---

    function decrementAvailable(bookId: string): void {
        const book = items.value.find((candidate) => candidate.id === bookId);
        if (book && book.availableCopies > 0) {
            book.availableCopies -= 1;
        }
    }

    async function loadBook(id: string): Promise<void> {
        currentLoading.value = true;
        currentError.value = '';

        try {
            current.value = await booksApi.getBook(id);
        } catch (caught) {
            currentError.value = isAppError(caught) ? caught.message : 'Failed to load this book.';
            current.value = null;
        } finally {
            currentLoading.value = false;
        }
    }

    function setCurrent(book: Book): void {
        current.value = book;
    }

    async function refreshBook(bookId: string): Promise<void> {
        try {
            const fresh = await booksApi.getBook(bookId);
            const index = items.value.findIndex((candidate) => candidate.id === bookId);
            if (index !== -1) {
                items.value[index] = fresh;
            }
        } catch {
            // Best-effort refresh; leave the existing row in place on failure.
        }
    }

    // --- Write operations (callers decide whether to refetch the list) ---

    async function rent(bookId: string): Promise<void> {
        await booksApi.rentBook(bookId);
    }

    /** Reflect a just-rented book locally so its "Already rented" state shows without a refetch. */
    function markRented(bookId: string): void {
        const book = items.value.find((candidate) => candidate.id === bookId);
        if (book) {
            book.rentedByCurrentUser = true;
        }
        if (current.value?.id === bookId) {
            current.value.rentedByCurrentUser = true;
        }
    }

    async function create(payload: BookFormPayload): Promise<Book> {
        return booksApi.createBook(payload);
    }

    async function update(id: string, payload: Partial<BookFormPayload>): Promise<Book> {
        return booksApi.updateBook(id, payload);
    }

    async function remove(id: string): Promise<void> {
        await booksApi.deleteBook(id);
    }

    return {
        items,
        meta,
        loading,
        error,
        params,
        current,
        currentLoading,
        currentError,
        fetch,
        loadBook,
        setCurrent,
        markRented,
        setSearch,
        setSearchField,
        setGenre,
        setAvailableOnly,
        setSort,
        goToPage,
        decrementAvailable,
        refreshBook,
        rent,
        create,
        update,
        remove,
    };
}
