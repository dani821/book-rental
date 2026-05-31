<script setup lang="ts">
import { type HTMLAttributes } from 'vue';

import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        for?: string;
        required?: boolean;
        class?: HTMLAttributes['class'];
    }>(),
    {
        for: undefined,
        required: false,
        class: undefined,
    },
);
</script>

<template>
    <label :for="props.for" :class="cn('text-sm leading-none font-medium select-none', props.class)">
        <slot />
        <span v-if="required" class="ml-0.5 text-destructive" aria-hidden="true">*</span>
    </label>
</template>
