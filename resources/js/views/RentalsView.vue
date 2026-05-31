<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { BookOpen, LoaderCircle } from '@lucide/vue';

import ProgressDialog from '@/components/rentals/ProgressDialog.vue';
import RentalCard from '@/components/rentals/RentalCard.vue';
import Alert from '@/components/common/Alert.vue';
import ConfirmDialog from '@/components/common/ConfirmDialog.vue';
import Pagination from '@/components/common/Pagination.vue';
import AppShell from '@/components/layout/AppShell.vue';
import { buttonVariants } from '@/components/ui/button';
import { useRentals } from '@/composables/useRentals';
import { isAppError } from '@/types/api';
import { type Rental } from '@/types/rental';

const rentals = useRentals();

type Feedback = { variant: 'success' | 'error'; message: string };
const feedback = ref<Feedback | null>(null);

// --- Extend (inline action) ---
const extendingId = ref<string | null>(null);

async function onExtend(rental: Rental): Promise<void> {
    extendingId.value = rental.id;
    feedback.value = null;

    try {
        await rentals.extend(rental.id);
        feedback.value = { variant: 'success', message: `Extended “${rental.book?.title ?? 'rental'}” by two weeks.` };
    } catch (error) {
        feedback.value = { variant: 'error', message: isAppError(error) ? error.message : 'Could not extend this rental.' };
    } finally {
        extendingId.value = null;
    }
}

// --- Log progress (dialog) ---
const progressOpen = ref(false);
const progressTarget = ref<Rental | null>(null);

function openProgress(rental: Rental): void {
    progressTarget.value = rental;
    progressOpen.value = true;
}

function submitProgress(currentPage: number): Promise<unknown> {
    if (!progressTarget.value) {
        return Promise.reject(new Error('No rental selected.'));
    }
    return rentals.updateProgress(progressTarget.value.id, currentPage);
}

function onProgressSaved(): void {
    feedback.value = { variant: 'success', message: 'Reading progress saved.' };
}

// --- Finish (confirm dialog) ---
const finishOpen = ref(false);
const finishTarget = ref<Rental | null>(null);
const finishLoading = ref(false);
const finishError = ref('');

function askFinish(rental: Rental): void {
    finishTarget.value = rental;
    finishError.value = '';
    finishOpen.value = true;
}

async function confirmFinish(): Promise<void> {
    if (!finishTarget.value) {
        return;
    }

    finishLoading.value = true;
    finishError.value = '';

    try {
        await rentals.finish(finishTarget.value.id);
        feedback.value = { variant: 'success', message: `Returned “${finishTarget.value.book?.title ?? 'book'}”.` };
        finishOpen.value = false;
    } catch (error) {
        finishError.value = isAppError(error) ? error.message : 'Could not finish this rental.';
    } finally {
        finishLoading.value = false;
    }
}

onMounted(() => {
    void rentals.fetch();
});
</script>

<template>
    <AppShell>
        <div class="space-y-6">
            <header class="space-y-1">
                <h1 class="text-2xl font-semibold tracking-tight">My rentals</h1>
                <p class="text-sm text-muted-foreground">Track your borrowed books, log your reading, and return them when you're done.</p>
            </header>

            <Alert v-if="feedback" :variant="feedback.variant">{{ feedback.message }}</Alert>

            <div v-if="rentals.loading.value" class="flex items-center justify-center gap-2 py-20 text-sm text-muted-foreground">
                <LoaderCircle class="size-5 animate-spin" />
                Loading your rentals…
            </div>

            <Alert v-else-if="rentals.error.value">{{ rentals.error.value }}</Alert>

            <div
                v-else-if="rentals.items.value.length === 0"
                class="flex flex-col items-center gap-3 rounded-xl border border-dashed py-20 text-center"
            >
                <span class="flex size-12 items-center justify-center rounded-full bg-secondary text-secondary-foreground">
                    <BookOpen class="size-6" />
                </span>
                <p class="font-medium">No rentals yet</p>
                <p class="max-w-sm text-sm text-muted-foreground">Browse the catalogue and rent a book to start reading.</p>
                <RouterLink :to="{ name: 'books' }" :class="buttonVariants({ size: 'sm' })">Browse books</RouterLink>
            </div>

            <template v-else>
                <div class="grid gap-4 lg:grid-cols-2">
                    <RentalCard
                        v-for="rental in rentals.items.value"
                        :key="rental.id"
                        :rental="rental"
                        :extending="extendingId === rental.id"
                        :finishing="finishLoading && finishTarget?.id === rental.id"
                        @update-progress="openProgress"
                        @extend="onExtend"
                        @finish="askFinish"
                    />
                </div>

                <Pagination
                    :current-page="rentals.meta.value.currentPage"
                    :last-page="rentals.meta.value.lastPage"
                    :total="rentals.meta.value.total"
                    :from="rentals.meta.value.from"
                    :to="rentals.meta.value.to"
                    @change="rentals.goToPage"
                />
            </template>
        </div>

        <ProgressDialog v-model:open="progressOpen" :rental="progressTarget" :submit="submitProgress" @saved="onProgressSaved" />
        <ConfirmDialog
            v-model:open="finishOpen"
            title="Finish rental"
            :description="finishTarget?.book ? `Mark “${finishTarget.book.title}” as finished and return it?` : undefined"
            confirm-label="Finish rental"
            :loading="finishLoading"
            :error="finishError"
            @confirm="confirmFinish"
        />
    </AppShell>
</template>
