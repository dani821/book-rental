<script setup lang="ts">
import { KeyRound, Trash2 } from '@lucide/vue';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/utils';
import { type User } from '@/types/user';

defineProps<{
    users: User[];
    currentUserId: string;
}>();

const emit = defineEmits<{
    resetPassword: [user: User];
    delete: [user: User];
}>();
</script>

<template>
    <div class="overflow-hidden rounded-xl border">
        <table class="w-full text-sm">
            <thead class="border-b bg-muted/40 text-left text-xs text-muted-foreground uppercase">
                <tr>
                    <th class="px-4 py-3 font-medium">Name</th>
                    <th class="hidden px-4 py-3 font-medium sm:table-cell">Email</th>
                    <th class="px-4 py-3 font-medium">Role</th>
                    <th class="hidden px-4 py-3 font-medium md:table-cell">Joined</th>
                    <th class="px-4 py-3 text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <tr v-for="user in users" :key="user.id" class="hover:bg-muted/30">
                    <td class="px-4 py-3">
                        <span class="font-medium">{{ user.name }}</span>
                        <p class="text-xs text-muted-foreground sm:hidden">{{ user.email }}</p>
                    </td>
                    <td class="hidden px-4 py-3 text-muted-foreground sm:table-cell">{{ user.email }}</td>
                    <td class="px-4 py-3">
                        <Badge :variant="user.role === 'admin' ? 'default' : 'secondary'" class="capitalize">{{ user.role }}</Badge>
                    </td>
                    <td class="hidden px-4 py-3 text-muted-foreground md:table-cell">{{ formatDate(user.createdAt) }}</td>
                    <td class="px-4 py-3">
                        <div v-if="user.id !== currentUserId" class="flex justify-end gap-1">
                            <Button
                                variant="ghost"
                                size="icon"
                                aria-label="Reset password"
                                title="Reset password"
                                @click="emit('resetPassword', user)"
                            >
                                <KeyRound class="size-4" />
                            </Button>
                            <Button variant="ghost" size="icon" aria-label="Delete user" title="Delete user" @click="emit('delete', user)">
                                <Trash2 class="size-4 text-destructive" />
                            </Button>
                        </div>
                        <div v-else class="flex justify-end">
                            <Badge variant="outline">You</Badge>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
