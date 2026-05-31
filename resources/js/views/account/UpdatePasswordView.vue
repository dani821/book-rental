<script setup lang="ts">
import { reactive, ref } from 'vue';
import { LoaderCircle } from '@lucide/vue';

import Alert from '@/components/common/Alert.vue';
import FormField from '@/components/forms/FormField.vue';
import AppShell from '@/components/layout/AppShell.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useApiError } from '@/composables/useApiError';
import { useAuth } from '@/composables/useAuth';

const { updatePassword } = useAuth();
const { fieldErrors, generalMessage, setFromError, clearField, clear } = useApiError();

const form = reactive({
    current_password: '',
    password: '',
    password_confirmation: '',
});
const submitting = ref(false);
const succeeded = ref(false);

function resetForm(): void {
    form.current_password = '';
    form.password = '';
    form.password_confirmation = '';
}

async function onSubmit(): Promise<void> {
    submitting.value = true;
    succeeded.value = false;

    try {
        await updatePassword({ ...form });
        clear();
        resetForm();
        succeeded.value = true;
    } catch (error) {
        setFromError(error);
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <AppShell>
        <div class="mx-auto max-w-xl space-y-6">
            <header class="space-y-1">
                <h1 class="text-2xl font-semibold tracking-tight">Account</h1>
                <p class="text-sm text-muted-foreground">Update the password you use to sign in.</p>
            </header>

            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Change password</CardTitle>
                    <CardDescription>Enter your current password, then choose a new one.</CardDescription>
                </CardHeader>

                <CardContent>
                    <form class="grid gap-4" novalidate @submit.prevent="onSubmit">
                        <Alert v-if="succeeded" variant="success">Your password has been updated.</Alert>
                        <Alert v-if="generalMessage">{{ generalMessage }}</Alert>

                        <FormField
                            v-model="form.current_password"
                            label="Current password"
                            type="password"
                            required
                            :error="fieldErrors.current_password"
                            @update:model-value="clearField('current_password')"
                        />

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

                        <div class="flex justify-end">
                            <Button type="submit" :disabled="submitting">
                                <LoaderCircle v-if="submitting" class="animate-spin" />
                                {{ submitting ? 'Saving…' : 'Update password' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppShell>
</template>
