<script setup lang="ts">
import { RouterLink } from 'vue-router';

import RentButton from '@/components/books/RentButton.vue';
import { Badge } from '@/components/ui/badge';
import { genreLabel, type Book } from '@/types/book';

const props = defineProps<{
    book: Book;
    renting?: boolean;
    alreadyRented?: boolean;
}>();

const emit = defineEmits<{ rent: [book: Book] }>();
</script>

<template>
    <div class="flex flex-col rounded-xl border bg-card p-5 text-card-foreground shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <Badge>{{ genreLabel(book.genre) }}</Badge>
            <Badge :variant="book.availableCopies > 0 ? 'success' : 'destructive'">
                {{ book.availableCopies > 0 ? `${book.availableCopies} available` : 'Unavailable' }}
            </Badge>
        </div>

        <RouterLink
            :to="{ name: 'books.show', params: { id: book.id } }"
            class="mt-3 line-clamp-2 font-semibold tracking-tight underline-offset-4 hover:underline"
        >
            {{ book.title }}
        </RouterLink>
        <p class="text-sm text-muted-foreground">by {{ book.author }}</p>

        <dl class="mt-4 flex flex-wrap gap-x-6 gap-y-1 text-xs text-muted-foreground">
            <div v-if="book.publishedYear" class="flex gap-1">
                <dt>Published</dt>
                <dd class="font-medium text-foreground">{{ book.publishedYear }}</dd>
            </div>
            <div class="flex gap-1">
                <dt>Pages</dt>
                <dd class="font-medium text-foreground">{{ book.totalPages }}</dd>
            </div>
            <div class="flex gap-1">
                <dt>Copies</dt>
                <dd class="font-medium text-foreground">{{ book.availableCopies }}/{{ book.totalCopies }}</dd>
            </div>
        </dl>

        <div class="mt-5 flex justify-end">
            <RentButton
                :available-copies="book.availableCopies"
                :loading="renting"
                :already-rented="alreadyRented"
                size="sm"
                @rent="emit('rent', props.book)"
            />
        </div>
    </div>
</template>
