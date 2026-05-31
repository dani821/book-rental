<script setup lang="ts">
import { type HTMLAttributes } from 'vue';

import { buttonVariants, type ButtonVariants } from '@/components/ui/button';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        variant?: ButtonVariants['variant'];
        size?: ButtonVariants['size'];
        type?: 'button' | 'submit' | 'reset';
        disabled?: boolean;
        class?: HTMLAttributes['class'];
    }>(),
    {
        variant: 'default',
        size: 'default',
        type: 'button',
        disabled: false,
        class: undefined,
    },
);
</script>

<template>
    <button :type="type" :disabled="disabled" :class="cn(buttonVariants({ variant, size }), props.class)">
        <slot />
    </button>
</template>
