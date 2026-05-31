<script setup lang="ts">
import { type HTMLAttributes } from 'vue';
import { ChevronDown } from '@lucide/vue';

import { cn } from '@/lib/utils';

defineOptions({ inheritAttrs: false });

const props = withDefaults(
    defineProps<{
        class?: HTMLAttributes['class'];
        disabled?: boolean;
    }>(),
    {
        class: undefined,
        disabled: false,
    },
);

const model = defineModel<string>();
</script>

<template>
    <div class="relative">
        <select
            v-model="model"
            :disabled="disabled"
            v-bind="$attrs"
            :class="
                cn(
                    'flex h-9 w-full cursor-pointer appearance-none rounded-md border border-input bg-transparent py-1 pr-8 pl-3 text-sm shadow-sm transition-colors focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50',
                    props.class,
                )
            "
        >
            <slot />
        </select>
        <ChevronDown class="pointer-events-none absolute top-1/2 right-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
    </div>
</template>
