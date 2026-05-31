<script setup lang="ts">
import BookCard from '@/components/books/BookCard.vue';
import { type Book } from '@/types/book';

defineProps<{
    books: Book[];
    rentingId?: string | null;
}>();

const emit = defineEmits<{ rent: [book: Book] }>();
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <BookCard
            v-for="book in books"
            :key="book.id"
            :book="book"
            :renting="rentingId === book.id"
            :already-rented="book.rentedByCurrentUser"
            @rent="emit('rent', $event)"
        />
    </div>
</template>
