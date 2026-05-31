<script setup lang="ts">
import { computed } from 'vue';
import { Search } from '@lucide/vue';

import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { BOOK_GENRES, BOOK_SORTS, type BookGenre, type BookSearchField } from '@/types/book';

const props = defineProps<{
    search: string;
    searchField: BookSearchField;
    genre: BookGenre | null;
    availableOnly: boolean;
    sort: string;
}>();

const emit = defineEmits<{
    'update:search': [value: string];
    'update:searchField': [value: BookSearchField];
    'update:genre': [value: BookGenre | null];
    'update:availableOnly': [value: boolean];
    'update:sort': [value: string];
}>();

const searchModel = computed({
    get: () => props.search,
    set: (value: string) => emit('update:search', value),
});

const searchFieldModel = computed({
    get: () => props.searchField,
    set: (value: string) => emit('update:searchField', value as BookSearchField),
});

const genreModel = computed({
    get: () => props.genre ?? '',
    set: (value: string) => emit('update:genre', value === '' ? null : (value as BookGenre)),
});

const sortModel = computed({
    get: () => props.sort,
    set: (value: string) => emit('update:sort', value),
});

const availableModel = computed({
    get: () => props.availableOnly,
    set: (value: boolean) => emit('update:availableOnly', value),
});
</script>

<template>
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-1 items-center gap-2">
            <div class="relative flex-1 sm:max-w-xs">
                <Search class="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input v-model="searchModel" type="search" :placeholder="`Search by ${searchField}…`" class="pl-8" aria-label="Search books" />
            </div>
            <Select v-model="searchFieldModel" class="w-32" aria-label="Search field">
                <option value="title">Title</option>
                <option value="author">Author</option>
            </Select>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <Select v-model="genreModel" class="w-40" aria-label="Filter by genre">
                <option value="">All genres</option>
                <option v-for="option in BOOK_GENRES" :key="option.value" :value="option.value">{{ option.label }}</option>
            </Select>

            <label class="flex h-9 cursor-pointer items-center gap-2 rounded-md border border-input px-3 text-sm whitespace-nowrap shadow-sm">
                <input v-model="availableModel" type="checkbox" class="size-4 cursor-pointer rounded border-input accent-primary" />
                Available only
            </label>

            <Select v-model="sortModel" class="w-44" aria-label="Sort books">
                <option v-for="option in BOOK_SORTS" :key="option.value" :value="option.value">{{ option.label }}</option>
            </Select>
        </div>
    </div>
</template>
