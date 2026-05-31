<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { BookMarked, LoaderCircle } from '@lucide/vue';

import Alert from '@/components/common/Alert.vue';
import FormField from '@/components/forms/FormField.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { useApiError } from '@/composables/useApiError';
import { useAuth } from '@/composables/useAuth';
import { APP_NAME } from '@/lib/constants';

const router = useRouter();
const { register } = useAuth();
const { fieldErrors, generalMessage, setFromError, clearField } = useApiError();

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});
const submitting = ref(false);

async function onSubmit(): Promise<void> {
    submitting.value = true;

    try {
        await register({ ...form });
        await router.push({ name: 'dashboard' });
    } catch (error) {
        setFromError(error);
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <div class="flex min-h-full flex-col items-center justify-center gap-6 bg-muted/30 px-4 py-12">
        <RouterLink :to="{ name: 'login' }" class="flex items-center gap-2 font-semibold tracking-tight">
            <span class="flex size-9 items-center justify-center rounded-md bg-primary text-primary-foreground">
                <BookMarked class="size-5" />
            </span>
            <span class="text-lg">{{ APP_NAME }}</span>
        </RouterLink>

        <Card class="w-full max-w-sm">
            <CardHeader class="text-center">
                <CardTitle class="text-xl">Create an account</CardTitle>
                <CardDescription>Start renting books on {{ APP_NAME }}</CardDescription>
            </CardHeader>

            <CardContent>
                <form class="grid gap-4" novalidate @submit.prevent="onSubmit">
                    <Alert v-if="generalMessage">{{ generalMessage }}</Alert>

                    <FormField
                        v-model="form.name"
                        label="Name"
                        placeholder="Ada Lovelace"
                        required
                        :error="fieldErrors.name"
                        @update:model-value="clearField('name')"
                    />

                    <FormField
                        v-model="form.email"
                        label="Email"
                        type="email"
                        placeholder="you@example.com"
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

                    <FormField
                        v-model="form.password_confirmation"
                        label="Confirm password"
                        type="password"
                        placeholder="Re-enter your password"
                        required
                        :error="fieldErrors.password_confirmation"
                        @update:model-value="clearField('password_confirmation')"
                    />

                    <Button type="submit" class="mt-2 w-full" :disabled="submitting">
                        <LoaderCircle v-if="submitting" class="animate-spin" />
                        {{ submitting ? 'Creating account…' : 'Create account' }}
                    </Button>
                </form>
            </CardContent>

            <CardFooter class="justify-center text-sm text-muted-foreground">
                <span>
                    Already have an account?
                    <RouterLink :to="{ name: 'login' }" class="font-medium text-foreground underline-offset-4 hover:underline"> Sign in </RouterLink>
                </span>
            </CardFooter>
        </Card>
    </div>
</template>
