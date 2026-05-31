<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import { LoaderCircle } from '@lucide/vue';

import Alert from '@/components/common/Alert.vue';
import FormField from '@/components/forms/FormField.vue';
import { Button } from '@/components/ui/button';
import { Dialog } from '@/components/ui/dialog';
import { useApiError } from '@/composables/useApiError';
import { type ResetPasswordPayload } from '@/api/users';
import { type User } from '@/types/user';

const props = defineProps<{
    user: User | null;
    submit: (payload: ResetPasswordPayload) => Promise<unknown>;
}>();

const open = defineModel<boolean>('open', { required: true });
const emit = defineEmits<{ saved: [] }>();

const { fieldErrors, generalMessage, setFromError, clearField, clear } = useApiError();

const submitting = ref(false);
const form = reactive({ password: '', password_confirmation: '' });

watch(open, (isOpen) => {
    if (!isOpen) {
        return;
    }

    clear();
    form.password = '';
    form.password_confirmation = '';
});

async function onSubmit(): Promise<void> {
    submitting.value = true;
    clear();

    try {
        await props.submit({ ...form });
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
    <Dialog v-model:open="open" title="Reset password" :description="user ? `Set a new password for ${user.name}.` : undefined">
        <form id="reset-password-form" class="grid gap-4" novalidate @submit.prevent="onSubmit">
            <Alert v-if="generalMessage">{{ generalMessage }}</Alert>

            <FormField
                v-model="form.password"
                label="New password"
                type="password"
                placeholder="At least 8 characters"
                required
                :error="fieldErrors.password"
                @update:model-value="clearField('password')"
            />

            <FormField
                v-model="form.password_confirmation"
                label="Confirm new password"
                type="password"
                required
                :error="fieldErrors.password_confirmation"
                @update:model-value="clearField('password_confirmation')"
            />
        </form>

        <template #footer>
            <Button variant="outline" :disabled="submitting" @click="open = false">Cancel</Button>
            <Button type="submit" form="reset-password-form" :disabled="submitting">
                <LoaderCircle v-if="submitting" class="animate-spin" />
                Reset password
            </Button>
        </template>
    </Dialog>
</template>
