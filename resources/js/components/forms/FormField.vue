<script setup lang="ts">
import { computed, useId } from 'vue';

import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

withDefaults(
    defineProps<{
        label: string;
        type?: string;
        error?: string;
        placeholder?: string;
        required?: boolean;
        disabled?: boolean;
    }>(),
    {
        type: 'text',
        error: undefined,
        placeholder: undefined,
        required: false,
        disabled: false,
    },
);

const model = defineModel<string>({ required: true });

const fieldId = useId();
const errorId = computed(() => `${fieldId}-error`);
</script>

<template>
    <div class="grid gap-2">
        <Label :for="fieldId" :required="required">{{ label }}</Label>
        <Input
            :id="fieldId"
            v-model="model"
            :type="type"
            :placeholder="placeholder"
            :required="required"
            :disabled="disabled"
            :aria-invalid="error ? true : undefined"
            :aria-describedby="error ? errorId : undefined"
            :class="cn(error && 'border-destructive focus-visible:ring-destructive')"
        />
        <p v-if="error" :id="errorId" class="text-sm text-destructive">{{ error }}</p>
    </div>
</template>
