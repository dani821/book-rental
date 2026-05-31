<script setup lang="ts">
import { computed } from 'vue';
import { ChevronLeft, ChevronRight } from '@lucide/vue';

import { Button } from '@/components/ui/button';

const props = defineProps<{
    currentPage: number;
    lastPage: number;
    total?: number;
    from?: number | null;
    to?: number | null;
}>();

const emit = defineEmits<{ change: [page: number] }>();

/** A compact, windowed list of page numbers with `null` marking an ellipsis gap. */
const pages = computed<(number | null)[]>(() => {
    const last = props.lastPage;
    const current = props.currentPage;

    if (last <= 7) {
        return Array.from({ length: last }, (_, index) => index + 1);
    }

    const result: (number | null)[] = [1];
    const start = Math.max(2, current - 1);
    const end = Math.min(last - 1, current + 1);

    if (start > 2) {
        result.push(null);
    }
    for (let page = start; page <= end; page += 1) {
        result.push(page);
    }
    if (end < last - 1) {
        result.push(null);
    }
    result.push(last);

    return result;
});

function go(page: number): void {
    if (page >= 1 && page <= props.lastPage && page !== props.currentPage) {
        emit('change', page);
    }
}
</script>

<template>
    <nav v-if="lastPage > 1" class="flex flex-wrap items-center justify-between gap-3" aria-label="Pagination">
        <p v-if="total !== undefined" class="text-sm text-muted-foreground">
            Showing <span class="font-medium text-foreground">{{ from ?? 0 }}</span
            >–<span class="font-medium text-foreground">{{ to ?? 0 }}</span> of <span class="font-medium text-foreground">{{ total }}</span>
        </p>

        <div class="flex items-center gap-1">
            <Button
                variant="outline"
                size="icon"
                :disabled="currentPage <= 1"
                aria-label="Previous page"
                title="Previous page"
                @click="go(currentPage - 1)"
            >
                <ChevronLeft class="size-4" />
            </Button>

            <template v-for="(page, index) in pages" :key="index">
                <span v-if="page === null" class="px-2 text-sm text-muted-foreground">…</span>
                <Button
                    v-else
                    :variant="page === currentPage ? 'default' : 'outline'"
                    size="icon"
                    :aria-current="page === currentPage ? 'page' : undefined"
                    @click="go(page)"
                >
                    {{ page }}
                </Button>
            </template>

            <Button
                variant="outline"
                size="icon"
                :disabled="currentPage >= lastPage"
                aria-label="Next page"
                title="Next page"
                @click="go(currentPage + 1)"
            >
                <ChevronRight class="size-4" />
            </Button>
        </div>
    </nav>
</template>
