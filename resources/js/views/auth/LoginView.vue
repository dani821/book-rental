<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { BookMarked, LoaderCircle } from '@lucide/vue';

import Alert from '@/components/common/Alert.vue';
import FormField from '@/components/forms/FormField.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { useApiError } from '@/composables/useApiError';
import { useAuth } from '@/composables/useAuth';
import { APP_NAME } from '@/lib/constants';

const router = useRouter();
const route = useRoute();
const { login } = useAuth();
const { fieldErrors, generalMessage, setFromError, clearField } = useApiError();

const form = reactive({ email: '', password: '' });
const submitting = ref(false);

async function onSubmit(): Promise<void> {
    submitting.value = true;

    try {
        await login({ email: form.email, password: form.password });
        const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/dashboard';
        await router.push(redirect);
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
                <CardTitle class="text-xl">Welcome back</CardTitle>
                <CardDescription>Sign in to your {{ APP_NAME }} account</CardDescription>
            </CardHeader>

            <CardContent>
                <form class="grid gap-4" novalidate @submit.prevent="onSubmit">
                    <Alert v-if="generalMessage">{{ generalMessage }}</Alert>

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
                        placeholder="••••••••"
                        required
                        :error="fieldErrors.password"
                        @update:model-value="clearField('password')"
                    />

                    <Button type="submit" class="mt-2 w-full" :disabled="submitting">
                        <LoaderCircle v-if="submitting" class="animate-spin" />
                        {{ submitting ? 'Signing in…' : 'Sign in' }}
                    </Button>
                </form>
            </CardContent>

            <CardFooter class="justify-center text-sm text-muted-foreground">
                <span>
                    Don't have an account?
                    <RouterLink :to="{ name: 'register' }" class="font-medium text-foreground underline-offset-4 hover:underline">
                        Create one
                    </RouterLink>
                </span>
            </CardFooter>
        </Card>
    </div>
</template>
