<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import { CircleAlert, CircleCheck } from '@lucide/vue';

import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        variant?: 'error' | 'success';
        class?: HTMLAttributes['class'];
    }>(),
    {
        variant: 'error',
        class: undefined,
    },
);

const icon = computed(() => (props.variant === 'success' ? CircleCheck : CircleAlert));
const variantClasses = computed(() =>
    props.variant === 'success' ? 'border-success/30 bg-success/10 text-success' : 'border-destructive/30 bg-destructive/10 text-destructive',
);
</script>

<template>
    <div role="alert" :class="cn('flex items-start gap-2 rounded-md border px-3 py-2 text-sm', variantClasses, props.class)">
        <component :is="icon" class="mt-0.5 size-4 shrink-0" />
        <div class="min-w-0"><slot /></div>
    </div>
</template>
