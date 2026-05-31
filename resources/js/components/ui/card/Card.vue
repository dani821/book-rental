<script setup lang="ts">
import { type HTMLAttributes } from 'vue';

import { cn } from '@/lib/utils';

const props = withDefaults(defineProps<{ class?: HTMLAttributes['class'] }>(), { class: undefined });
</script>

<template>
    <div :class="cn('rounded-xl border bg-card text-card-foreground shadow-sm', props.class)">
        <slot />
    </div>
</template>
