<script setup lang="ts">
import { type HTMLAttributes } from 'vue';

import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        type?: string;
        class?: HTMLAttributes['class'];
    }>(),
    {
        type: 'text',
        class: undefined,
    },
);

const model = defineModel<string>();
</script>

<template>
    <input
        v-model="model"
        :type="type"
        :class="
            cn(
                'flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50',
                props.class,
            )
        "
    />
</template>
