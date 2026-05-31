<script setup lang="ts">
import { ref, watch } from 'vue';
import { LoaderCircle } from '@lucide/vue';

import Alert from '@/components/common/Alert.vue';
import FormField from '@/components/forms/FormField.vue';
import { Button } from '@/components/ui/button';
import { Dialog } from '@/components/ui/dialog';
import { useApiError } from '@/composables/useApiError';
import { type Rental } from '@/types/rental';

const props = defineProps<{
    rental: Rental | null;
    submit: (currentPage: number) => Promise<unknown>;
}>();

const open = defineModel<boolean>('open', { required: true });
const emit = defineEmits<{ saved: [] }>();

const { fieldErrors, generalMessage, setFromError, clearField, clear } = useApiError();

const submitting = ref(false);
const currentPage = ref('');

watch(open, (isOpen) => {
    if (!isOpen) {
        return;
    }

    clear();
    currentPage.value = props.rental ? String(props.rental.currentPage) : '';
});

async function onSubmit(): Promise<void> {
    submitting.value = true;
    clear();

    try {
        await props.submit(Number(currentPage.value));
        emit('saved');
        open.value = false;
    } catch (error) {
        setFromError(error);
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <Dialog
        v-model:open="open"
        title="Log reading progress"
        :description="rental?.book ? `How far are you through “${rental.book.title}”?` : undefined"
    >
        <form id="progress-form" class="grid gap-4" novalidate @submit.prevent="onSubmit">
            <Alert v-if="generalMessage">{{ generalMessage }}</Alert>

            <FormField
                v-model="currentPage"
                label="Current page"
                type="number"
                :placeholder="rental?.book ? `1 – ${rental.book.totalPages}` : undefined"
                required
                :error="fieldErrors.current_page"
                @update:model-value="clearField('current_page')"
            />
        </form>

        <template #footer>
            <Button variant="outline" :disabled="submitting" @click="open = false">Cancel</Button>
            <Button type="submit" form="progress-form" :disabled="submitting">
                <LoaderCircle v-if="submitting" class="animate-spin" />
                Save progress
            </Button>
        </template>
    </Dialog>
</template>
