<script setup lang="ts">
import { LoaderCircle } from '@lucide/vue';

import Alert from '@/components/common/Alert.vue';
import { Button } from '@/components/ui/button';
import { Dialog } from '@/components/ui/dialog';

withDefaults(
    defineProps<{
        title: string;
        description?: string;
        confirmLabel?: string;
        cancelLabel?: string;
        loading?: boolean;
        error?: string;
        destructive?: boolean;
    }>(),
    {
        description: undefined,
        confirmLabel: 'Confirm',
        cancelLabel: 'Cancel',
        loading: false,
        error: undefined,
        destructive: false,
    },
);

const open = defineModel<boolean>('open', { required: true });
const emit = defineEmits<{ confirm: [] }>();
</script>

<template>
    <Dialog v-model:open="open" :title="title" :description="description">
        <Alert v-if="error">{{ error }}</Alert>

        <template #footer>
            <Button variant="outline" :disabled="loading" @click="open = false">{{ cancelLabel }}</Button>
            <Button :variant="destructive ? 'destructive' : 'default'" :disabled="loading" @click="emit('confirm')">
                <LoaderCircle v-if="loading" class="animate-spin" />
                {{ confirmLabel }}
            </Button>
        </template>
    </Dialog>
</template>
