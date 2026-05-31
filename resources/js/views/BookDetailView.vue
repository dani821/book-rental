<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ArrowLeft, LoaderCircle, Pencil, Trash2 } from '@lucide/vue';

import BookFormDialog from '@/components/books/BookFormDialog.vue';
import RentButton from '@/components/books/RentButton.vue';
import Alert from '@/components/common/Alert.vue';
import ConfirmDialog from '@/components/common/ConfirmDialog.vue';
import AppShell from '@/components/layout/AppShell.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { useAuth } from '@/composables/useAuth';
import { useBooks } from '@/composables/useBooks';
import { isAppError } from '@/types/api';
import { genreLabel, type BookFormPayload } from '@/types/book';

const route = useRoute();
const router = useRouter();
const { isAdmin } = useAuth();
const books = useBooks();

const bookId = String(route.params.id);
const book = books.current;

type Feedback = { variant: 'success' | 'error'; message: string };
const feedback = ref<Feedback | null>(null);

const details = computed(() => {
    if (!book.value) {
        return [];
    }
    return [
        { label: 'Genre', value: genreLabel(book.value.genre) },
        { label: 'ISBN', value: book.value.isbn ?? '—' },
        { label: 'Published', value: book.value.publishedYear?.toString() ?? '—' },
        { label: 'Pages', value: book.value.totalPages.toString() },
        { label: 'Available', value: `${book.value.availableCopies} of ${book.value.totalCopies}` },
    ];
});

// --- Member rent ---
const renting = ref(false);
const alreadyRented = computed(() => book.value?.rentedByCurrentUser ?? false);

async function onRent(): Promise<void> {
    if (!book.value) {
        return;
    }

    renting.value = true;
    feedback.value = null;

    try {
        await books.rent(book.value.id);
        books.markRented(book.value.id);
        await books.loadBook(book.value.id);
        feedback.value = { variant: 'success', message: 'Book rented. Check your rentals to start reading.' };
    } catch (error) {
        feedback.value = { variant: 'error', message: isAppError(error) ? error.message : 'Could not rent this book.' };
        if (isAppError(error) && error.status === 409 && /active rental/i.test(error.message)) {
            books.markRented(book.value.id);
        }
        await books.loadBook(bookId);
    } finally {
        renting.value = false;
    }
}

// --- Admin edit / delete ---
const formOpen = ref(false);

async function submitBook(payload: BookFormPayload): Promise<unknown> {
    const updated = await books.update(bookId, payload);
    books.setCurrent(updated);
    return updated;
}

function onSaved(): void {
    feedback.value = { variant: 'success', message: 'Book updated.' };
}

const confirmOpen = ref(false);
const deleteLoading = ref(false);
const deleteError = ref('');

async function confirmDelete(): Promise<void> {
    deleteLoading.value = true;
    deleteError.value = '';

    try {
        await books.remove(bookId);
        await router.push({ name: 'books' });
    } catch (error) {
        deleteError.value = isAppError(error) ? error.message : 'Could not delete this book.';
    } finally {
        deleteLoading.value = false;
    }
}

onMounted(() => {
    void books.loadBook(bookId);
});
</script>

<template>
    <AppShell>
        <div class="mx-auto max-w-3xl space-y-6">
            <RouterLink :to="{ name: 'books' }" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground">
                <ArrowLeft class="size-4" />
                Back to books
            </RouterLink>

            <div v-if="books.currentLoading.value" class="flex items-center justify-center gap-2 py-20 text-sm text-muted-foreground">
                <LoaderCircle class="size-5 animate-spin" />
                Loading book…
            </div>

            <Alert v-else-if="books.currentError.value">{{ books.currentError.value }}</Alert>

            <template v-else-if="book">
                <Alert v-if="feedback" :variant="feedback.variant">{{ feedback.message }}</Alert>

                <Card>
                    <CardContent class="space-y-6 pt-6">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <Badge>{{ genreLabel(book.genre) }}</Badge>
                                    <Badge :variant="book.availableCopies > 0 ? 'success' : 'destructive'">
                                        {{ book.availableCopies > 0 ? `${book.availableCopies} available` : 'Unavailable' }}
                                    </Badge>
                                </div>
                                <h1 class="text-2xl font-semibold tracking-tight">{{ book.title }}</h1>
                                <p class="text-muted-foreground">by {{ book.author }}</p>
                            </div>

                            <RentButton
                                v-if="!isAdmin"
                                :available-copies="book.availableCopies"
                                :loading="renting"
                                :already-rented="alreadyRented"
                                @rent="onRent"
                            />
                            <div v-else class="flex gap-2">
                                <Button variant="outline" class="gap-2" @click="formOpen = true">
                                    <Pencil class="size-4" />
                                    Edit
                                </Button>
                                <Button variant="outline" class="gap-2 text-destructive" @click="confirmOpen = true">
                                    <Trash2 class="size-4" />
                                    Delete
                                </Button>
                            </div>
                        </div>

                        <dl class="grid gap-4 border-t pt-6 sm:grid-cols-2">
                            <div v-for="detail in details" :key="detail.label">
                                <dt class="text-xs font-medium text-muted-foreground uppercase">{{ detail.label }}</dt>
                                <dd class="mt-1 text-sm">{{ detail.value }}</dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>
            </template>
        </div>

        <template v-if="isAdmin && book">
            <BookFormDialog v-model:open="formOpen" :book="book" :submit="submitBook" @saved="onSaved" />
            <ConfirmDialog
                v-model:open="confirmOpen"
                title="Delete book"
                :description="`Permanently delete “${book.title}”? This can't be undone.`"
                confirm-label="Delete"
                destructive
                :loading="deleteLoading"
                :error="deleteError"
                @confirm="confirmDelete"
            />
        </template>
    </AppShell>
</template>
