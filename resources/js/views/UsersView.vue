<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { LoaderCircle, Plus, Users as UsersIcon } from '@lucide/vue';

import ResetPasswordDialog from '@/components/users/ResetPasswordDialog.vue';
import UserFormDialog from '@/components/users/UserFormDialog.vue';
import UserTable from '@/components/users/UserTable.vue';
import Alert from '@/components/common/Alert.vue';
import ConfirmDialog from '@/components/common/ConfirmDialog.vue';
import Pagination from '@/components/common/Pagination.vue';
import AppShell from '@/components/layout/AppShell.vue';
import { Button } from '@/components/ui/button';
import { useAuth } from '@/composables/useAuth';
import { useUsers } from '@/composables/useUsers';
import { type CreateUserPayload, type ResetPasswordPayload } from '@/api/users';
import { isAppError } from '@/types/api';
import { type User } from '@/types/user';

const { user: currentUser } = useAuth();
const users = useUsers();

type Feedback = { variant: 'success' | 'error'; message: string };
const feedback = ref<Feedback | null>(null);

// --- Create ---
const formOpen = ref(false);

function submitNewUser(payload: CreateUserPayload): Promise<unknown> {
    return users.create(payload);
}

function onCreated(): void {
    feedback.value = { variant: 'success', message: 'User created.' };
    void users.fetch();
}

// --- Reset password ---
const resetOpen = ref(false);
const resetTarget = ref<User | null>(null);

function openReset(user: User): void {
    resetTarget.value = user;
    resetOpen.value = true;
}

function submitReset(payload: ResetPasswordPayload): Promise<unknown> {
    if (!resetTarget.value) {
        return Promise.reject(new Error('No user selected.'));
    }
    return users.resetPassword(resetTarget.value.id, payload);
}

function onReset(): void {
    feedback.value = { variant: 'success', message: `Password reset for ${resetTarget.value?.name}.` };
}

// --- Delete ---
const confirmOpen = ref(false);
const deletingUser = ref<User | null>(null);
const deleteLoading = ref(false);
const deleteError = ref('');

function askDelete(user: User): void {
    deletingUser.value = user;
    deleteError.value = '';
    confirmOpen.value = true;
}

async function confirmDelete(): Promise<void> {
    if (!deletingUser.value) {
        return;
    }

    deleteLoading.value = true;
    deleteError.value = '';

    try {
        await users.remove(deletingUser.value.id);
        feedback.value = { variant: 'success', message: `Deleted ${deletingUser.value.name}.` };
        confirmOpen.value = false;
        void users.fetch();
    } catch (error) {
        deleteError.value = isAppError(error) ? error.message : 'Could not delete this user.';
    } finally {
        deleteLoading.value = false;
    }
}

onMounted(() => {
    void users.fetch();
});
</script>

<template>
    <AppShell>
        <div class="space-y-6">
            <header class="flex flex-wrap items-end justify-between gap-3">
                <div class="space-y-1">
                    <h1 class="text-2xl font-semibold tracking-tight">Users</h1>
                    <p class="text-sm text-muted-foreground">Manage member and admin accounts.</p>
                </div>
                <Button class="gap-2" @click="formOpen = true">
                    <Plus class="size-4" />
                    New user
                </Button>
            </header>

            <Alert v-if="feedback" :variant="feedback.variant">{{ feedback.message }}</Alert>

            <div v-if="users.loading.value" class="flex items-center justify-center gap-2 py-20 text-sm text-muted-foreground">
                <LoaderCircle class="size-5 animate-spin" />
                Loading users…
            </div>

            <Alert v-else-if="users.error.value">{{ users.error.value }}</Alert>

            <div
                v-else-if="users.items.value.length === 0"
                class="flex flex-col items-center gap-3 rounded-xl border border-dashed py-20 text-center"
            >
                <span class="flex size-12 items-center justify-center rounded-full bg-secondary text-secondary-foreground">
                    <UsersIcon class="size-6" />
                </span>
                <p class="font-medium">No users yet</p>
            </div>

            <template v-else>
                <UserTable :users="users.items.value" :current-user-id="currentUser?.id ?? ''" @reset-password="openReset" @delete="askDelete" />
                <Pagination
                    :current-page="users.meta.value.currentPage"
                    :last-page="users.meta.value.lastPage"
                    :total="users.meta.value.total"
                    :from="users.meta.value.from"
                    :to="users.meta.value.to"
                    @change="users.goToPage"
                />
            </template>
        </div>

        <UserFormDialog v-model:open="formOpen" :submit="submitNewUser" @saved="onCreated" />
        <ResetPasswordDialog v-model:open="resetOpen" :user="resetTarget" :submit="submitReset" @saved="onReset" />
        <ConfirmDialog
            v-model:open="confirmOpen"
            title="Delete user"
            :description="deletingUser ? `Permanently delete ${deletingUser.name}? This can't be undone.` : undefined"
            confirm-label="Delete"
            destructive
            :loading="deleteLoading"
            :error="deleteError"
            @confirm="confirmDelete"
        />
    </AppShell>
</template>
