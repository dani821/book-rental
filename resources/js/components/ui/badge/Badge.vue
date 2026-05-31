<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';

import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        variant?: 'default' | 'secondary' | 'outline' | 'success' | 'destructive';
        class?: HTMLAttributes['class'];
    }>(),
    {
        variant: 'secondary',
        class: undefined,
    },
);

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'default':
            return 'border-transparent bg-primary text-primary-foreground';
        case 'outline':
            return 'text-foreground';
        case 'success':
            return 'border-transparent bg-success/10 text-success';
        case 'destructive':
            return 'border-transparent bg-destructive/10 text-destructive';
        default:
            return 'border-transparent bg-secondary text-secondary-foreground';
    }
});
</script>

<template>
    <span :class="cn('inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium whitespace-nowrap', variantClasses, props.class)">
        <slot />
    </span>
</template>
