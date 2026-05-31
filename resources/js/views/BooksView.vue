<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { LoaderCircle, Plus, SearchX } from '@lucide/vue';

import BookFilters from '@/components/books/BookFilters.vue';
import BookFormDialog from '@/components/books/BookFormDialog.vue';
import BookList from '@/components/books/BookList.vue';
import BookTable from '@/components/books/BookTable.vue';
import Alert from '@/components/common/Alert.vue';
import ConfirmDialog from '@/components/common/ConfirmDialog.vue';
import Pagination from '@/components/common/Pagination.vue';
import AppShell from '@/components/layout/AppShell.vue';
import { Button } from '@/components/ui/button';
import { useAuth } from '@/composables/useAuth';
import { useBooks } from '@/composables/useBooks';
import { isAppError } from '@/types/api';
import { type Book, type BookFormPayload } from '@/types/book';

const { isAdmin } = useAuth();
const books = useBooks();

type Feedback = { variant: 'success' | 'error'; message: string };
const feedback = ref<Feedback | null>(null);

// --- Member rent flow ---
const rentingId = ref<string | null>(null);

async function onRent(book: Book): Promise<void> {
    rentingId.value = book.id;
    feedback.value = null;

    try {
        await books.rent(book.id);
        books.decrementAvailable(book.id);
        books.markRented(book.id);
        feedback.value = { variant: 'success', message: `You rented “${book.title}”.` };
    } catch (error) {
        const message = isAppError(error) ? error.message : 'Could not rent this book. Please try again.';
        feedback.value = { variant: 'error', message };

        if (isAppError(error) && error.status === 409 && /active rental/i.test(error.message)) {
            books.markRented(book.id);
        }

        await books.refreshBook(book.id);
    } finally {
        rentingId.value = null;
    }
}

// --- Admin CRUD flow ---
const formOpen = ref(false);
const editingBook = ref<Book | null>(null);

function openCreate(): void {
    editingBook.value = null;
    formOpen.value = true;
}

function openEdit(book: Book): void {
    editingBook.value = book;
    formOpen.value = true;
}

function submitBook(payload: BookFormPayload): Promise<unknown> {
    return editingBook.value ? books.update(editingBook.value.id, payload) : books.create(payload);
}

function onSaved(isEdit: boolean): void {
    feedback.value = { variant: 'success', message: isEdit ? 'Book updated.' : 'Book created.' };
    void books.fetch();
}

const confirmOpen = ref(false);
const deletingBook = ref<Book | null>(null);
const deleteLoading = ref(false);
const deleteError = ref('');

function askDelete(book: Book): void {
    deletingBook.value = book;
    deleteError.value = '';
    confirmOpen.value = true;
}

async function confirmDelete(): Promise<void> {
    if (!deletingBook.value) {
        return;
    }

    deleteLoading.value = true;
    deleteError.value = '';

    try {
        await books.remove(deletingBook.value.id);
        feedback.value = { variant: 'success', message: `Deleted “${deletingBook.value.title}”.` };
        confirmOpen.value = false;
        void books.fetch();
    } catch (error) {
        deleteError.value = isAppError(error) ? error.message : 'Could not delete this book.';
    } finally {
        deleteLoading.value = false;
    }
}

onMounted(() => {
    void books.fetch();
});
</script>

<template>
    <AppShell>
        <div class="space-y-6">
            <header class="flex flex-wrap items-end justify-between gap-3">
                <div class="space-y-1">
                    <h1 class="text-2xl font-semibold tracking-tight">Books</h1>
                    <p class="text-sm text-muted-foreground">
                        {{ isAdmin ? 'Manage the library catalogue.' : 'Browse the catalogue and rent a title.' }}
                    </p>
                </div>
                <Button v-if="isAdmin" class="gap-2" @click="openCreate">
                    <Plus class="size-4" />
                    New book
                </Button>
            </header>

            <Alert v-if="feedback" :variant="feedback.variant">{{ feedback.message }}</Alert>

            <BookFilters
                :search="books.params.search"
                :search-field="books.params.searchField"
                :genre="books.params.genre"
                :available-only="books.params.availableOnly"
                :sort="books.params.sort"
                @update:search="books.setSearch"
                @update:search-field="books.setSearchField"
                @update:genre="books.setGenre"
                @update:available-only="books.setAvailableOnly"
                @update:sort="books.setSort"
            />

            <div v-if="books.loading.value" class="flex items-center justify-center gap-2 py-20 text-sm text-muted-foreground">
                <LoaderCircle class="size-5 animate-spin" />
                Loading books…
            </div>

            <Alert v-else-if="books.error.value">{{ books.error.value }}</Alert>

            <div
                v-else-if="books.items.value.length === 0"
                class="flex flex-col items-center gap-3 rounded-xl border border-dashed py-20 text-center"
            >
                <span class="flex size-12 items-center justify-center rounded-full bg-secondary text-secondary-foreground">
                    <SearchX class="size-6" />
                </span>
                <p class="font-medium">No books found</p>
                <p class="max-w-sm text-sm text-muted-foreground">Try adjusting your search or filters.</p>
            </div>

            <template v-else>
                <BookList v-if="!isAdmin" :books="books.items.value" :renting-id="rentingId" @rent="onRent" />
                <BookTable v-else :books="books.items.value" @edit="openEdit" @delete="askDelete" />
            </template>

            <Pagination
                :current-page="books.meta.value.currentPage"
                :last-page="books.meta.value.lastPage"
                :total="books.meta.value.total"
                :from="books.meta.value.from"
                :to="books.meta.value.to"
                @change="books.goToPage"
            />
        </div>

        <template v-if="isAdmin">
            <BookFormDialog v-model:open="formOpen" :book="editingBook" :submit="submitBook" @saved="onSaved" />
            <ConfirmDialog
                v-model:open="confirmOpen"
                title="Delete book"
                :description="deletingBook ? `Permanently delete “${deletingBook.title}”? This can't be undone.` : undefined"
                confirm-label="Delete"
                destructive
                :loading="deleteLoading"
                :error="deleteError"
                @confirm="confirmDelete"
            />
        </template>
    </AppShell>
</template>
