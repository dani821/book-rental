<script setup lang="ts">
import { RouterLink } from 'vue-router';
import { Pencil, Trash2 } from '@lucide/vue';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { genreLabel, type Book } from '@/types/book';

defineProps<{ books: Book[] }>();

const emit = defineEmits<{
    edit: [book: Book];
    delete: [book: Book];
}>();
</script>

<template>
    <div class="overflow-hidden rounded-xl border">
        <table class="w-full text-sm">
            <thead class="border-b bg-muted/40 text-left text-xs text-muted-foreground uppercase">
                <tr>
                    <th class="px-4 py-3 font-medium">Title</th>
                    <th class="hidden px-4 py-3 font-medium sm:table-cell">Author</th>
                    <th class="hidden px-4 py-3 font-medium md:table-cell">Genre</th>
                    <th class="hidden px-4 py-3 font-medium sm:table-cell">Year</th>
                    <th class="px-4 py-3 font-medium">Copies</th>
                    <th class="px-4 py-3 text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <tr v-for="book in books" :key="book.id" class="hover:bg-muted/30">
                    <td class="px-4 py-3">
                        <RouterLink :to="{ name: 'books.show', params: { id: book.id } }" class="font-medium underline-offset-4 hover:underline">
                            {{ book.title }}
                        </RouterLink>
                        <p class="text-xs text-muted-foreground sm:hidden">by {{ book.author }}</p>
                    </td>
                    <td class="hidden px-4 py-3 text-muted-foreground sm:table-cell">{{ book.author }}</td>
                    <td class="hidden px-4 py-3 md:table-cell">
                        <Badge>{{ genreLabel(book.genre) }}</Badge>
                    </td>
                    <td class="hidden px-4 py-3 text-muted-foreground sm:table-cell">{{ book.publishedYear ?? '—' }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span :class="book.availableCopies > 0 ? 'text-foreground' : 'text-destructive'">{{ book.availableCopies }}</span>
                        <span class="text-muted-foreground"> / {{ book.totalCopies }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-1">
                            <Button variant="ghost" size="icon" aria-label="Edit book" title="Edit book" @click="emit('edit', book)">
                                <Pencil class="size-4" />
                            </Button>
                            <Button variant="ghost" size="icon" aria-label="Delete book" title="Delete book" @click="emit('delete', book)">
                                <Trash2 class="size-4 text-destructive" />
                            </Button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
