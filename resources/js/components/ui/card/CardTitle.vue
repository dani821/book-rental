<script setup lang="ts">
import { type HTMLAttributes } from 'vue';

import { cn } from '@/lib/utils';

const props = withDefaults(defineProps<{ class?: HTMLAttributes['class'] }>(), { class: undefined });
</script>

<template>
    <h3 :class="cn('leading-none font-semibold tracking-tight', props.class)">
        <slot />
    </h3>
</template>
