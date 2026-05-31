<script setup lang="ts">
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import { CalendarClock, Check, LoaderCircle } from '@lucide/vue';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/utils';
import { MAX_RENTAL_EXTENSIONS, type Rental } from '@/types/rental';

const props = defineProps<{
    rental: Rental;
    extending?: boolean;
    finishing?: boolean;
}>();

const emit = defineEmits<{
    updateProgress: [rental: Rental];
    extend: [rental: Rental];
    finish: [rental: Rental];
}>();

const isActive = computed(() => props.rental.status === 'active');
const totalPages = computed(() => props.rental.book?.totalPages ?? 0);
const canExtend = computed(() => isActive.value && props.rental.extensionsCount < MAX_RENTAL_EXTENSIONS);
</script>

<template>
    <div class="flex flex-col rounded-xl border bg-card p-5 text-card-foreground shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <RouterLink
                    v-if="rental.book"
                    :to="{ name: 'books.show', params: { id: rental.book.id } }"
                    class="line-clamp-2 font-semibold tracking-tight underline-offset-4 hover:underline"
                >
                    {{ rental.book.title }}
                </RouterLink>
                <span v-else class="font-semibold">Unknown book</span>
                <p class="text-sm text-muted-foreground">by {{ rental.book?.author ?? '—' }}</p>
            </div>
            <Badge :variant="isActive ? 'success' : 'secondary'" class="capitalize">{{ rental.status }}</Badge>
        </div>

        <div class="mt-4 space-y-1.5">
            <div class="flex items-center justify-between text-xs text-muted-foreground">
                <span>Reading progress</span>
                <span class="font-medium text-foreground">{{ rental.currentPage }} / {{ totalPages }} pages · {{ rental.progressPercentage }}%</span>
            </div>
            <div class="h-2 w-full overflow-hidden rounded-full bg-secondary">
                <div class="h-full rounded-full bg-primary transition-[width] duration-300" :style="{ width: `${rental.progressPercentage}%` }" />
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-muted-foreground">
            <span class="inline-flex items-center gap-1">
                <CalendarClock class="size-3.5" />
                <template v-if="isActive">Due {{ formatDate(rental.dueAt) }}</template>
                <template v-else>Returned {{ formatDate(rental.returnedAt) }}</template>
            </span>
            <span>{{ rental.extensionsCount }}/{{ MAX_RENTAL_EXTENSIONS }} extensions used</span>
        </div>

        <div v-if="isActive" class="mt-5 flex flex-wrap justify-end gap-2">
            <Button variant="outline" size="sm" :disabled="extending || finishing" @click="emit('updateProgress', rental)"> Log progress </Button>
            <Button variant="outline" size="sm" :disabled="!canExtend || extending || finishing" @click="emit('extend', rental)">
                <LoaderCircle v-if="extending" class="animate-spin" />
                {{ rental.extensionsCount >= MAX_RENTAL_EXTENSIONS ? 'Max extensions' : 'Extend' }}
            </Button>
            <Button size="sm" :disabled="extending || finishing" @click="emit('finish', rental)">
                <LoaderCircle v-if="finishing" class="animate-spin" />
                <Check v-else class="size-4" />
                Finish
            </Button>
        </div>
    </div>
</template>
