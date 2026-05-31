<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import { LoaderCircle } from '@lucide/vue';

import Alert from '@/components/common/Alert.vue';
import FormField from '@/components/forms/FormField.vue';
import { Button } from '@/components/ui/button';
import { Dialog } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
import { useApiError } from '@/composables/useApiError';
import { USER_ROLES, type UserRole } from '@/types/user';
import { type CreateUserPayload } from '@/api/users';

const props = defineProps<{
    submit: (payload: CreateUserPayload) => Promise<unknown>;
}>();

const open = defineModel<boolean>('open', { required: true });
const emit = defineEmits<{ saved: [] }>();

const { fieldErrors, generalMessage, setFromError, clearField, clear } = useApiError();

const submitting = ref(false);

const form = reactive({
    name: '',
    email: '',
    password: '',
    role: 'member' as UserRole,
});

const roleModel = computed({
    get: () => form.role,
    set: (value: string) => {
        form.role = value as UserRole;
    },
});

watch(open, (isOpen) => {
    if (!isOpen) {
        return;
    }

    clear();
    form.name = '';
    form.email = '';
    form.password = '';
    form.role = 'member';
});

async function onSubmit(): Promise<void> {
    submitting.value = true;
    clear();

    try {
        await props.submit({
            name: form.name.trim(),
            email: form.email.trim(),
            password: form.password,
            role: form.role,
        });
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
    <Dialog v-model:open="open" title="New user" description="Create a member or admin account.">
        <form id="user-form" class="grid gap-4" novalidate @submit.prevent="onSubmit">
            <Alert v-if="generalMessage">{{ generalMessage }}</Alert>

            <FormField v-model="form.name" label="Name" required :error="fieldErrors.name" @update:model-value="clearField('name')" />

            <FormField
                v-model="form.email"
                label="Email"
                type="email"
                required
                :error="fieldErrors.email"
                @update:model-value="clearField('email')"
            />

            <FormField
                v-model="form.password"
                label="Password"
                type="password"
                placeholder="At least 8 characters"
                required
                :error="fieldErrors.password"
                @update:model-value="clearField('password')"
            />

            <div class="grid gap-2">
                <Label>Role</Label>
                <Select v-model="roleModel" aria-label="Role" :class="fieldErrors.role ? 'border-destructive' : undefined">
                    <option v-for="option in USER_ROLES" :key="option.value" :value="option.value">{{ option.label }}</option>
                </Select>
                <p v-if="fieldErrors.role" class="text-sm text-destructive">{{ fieldErrors.role }}</p>
            </div>
        </form>

        <template #footer>
            <Button variant="outline" :disabled="submitting" @click="open = false">Cancel</Button>
            <Button type="submit" form="user-form" :disabled="submitting">
                <LoaderCircle v-if="submitting" class="animate-spin" />
                Create user
            </Button>
        </template>
    </Dialog>
</template>
