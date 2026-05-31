<script setup lang="ts">
import { computed } from 'vue';
import { Check, LoaderCircle } from '@lucide/vue';

import { Button } from '@/components/ui/button';
import { type ButtonVariants } from '@/components/ui/button';

const props = withDefaults(
    defineProps<{
        availableCopies: number;
        loading?: boolean;
        alreadyRented?: boolean;
        size?: ButtonVariants['size'];
    }>(),
    {
        loading: false,
        alreadyRented: false,
        size: 'default',
    },
);

const emit = defineEmits<{ rent: [] }>();

const isAvailable = computed(() => props.availableCopies > 0);
const disabled = computed(() => props.loading || props.alreadyRented || !isAvailable.value);
</script>

<template>
    <Button :size="size" :variant="alreadyRented ? 'secondary' : 'default'" :disabled="disabled" @click="emit('rent')">
        <LoaderCircle v-if="loading" class="animate-spin" />
        <Check v-else-if="alreadyRented" class="size-4" />
        <span v-if="loading">Renting…</span>
        <span v-else-if="alreadyRented">Already rented</span>
        <span v-else-if="!isAvailable">Not available</span>
        <span v-else>Rent</span>
    </Button>
</template>
