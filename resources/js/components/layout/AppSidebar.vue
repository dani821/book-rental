<script setup lang="ts">
import { computed, ref, type FunctionalComponent } from 'vue';
import { useRouter } from 'vue-router';
import { BookMarked, BookOpen, KeyRound, LayoutDashboard, LibraryBig, LogOut, Users } from '@lucide/vue';

import { Button } from '@/components/ui/button';
import { useAuth } from '@/composables/useAuth';
import { APP_NAME } from '@/lib/constants';
import { cn } from '@/lib/utils';

defineProps<{ open: boolean }>();
const emit = defineEmits<{ close: [] }>();

const router = useRouter();
const { user, isAdmin, logout } = useAuth();

interface NavItem {
    label: string;
    to: string;
    icon: FunctionalComponent;
}

const memberItems: NavItem[] = [
    { label: 'Books', to: '/books', icon: BookOpen },
    { label: 'Rentals', to: '/rentals', icon: LibraryBig },
];

const adminItems: NavItem[] = [
    { label: 'Users', to: '/users', icon: Users },
    { label: 'Books', to: '/books', icon: BookOpen },
];

const navItems = computed<NavItem[]>(() => [
    { label: 'Dashboard', to: '/dashboard', icon: LayoutDashboard },
    ...(isAdmin.value ? adminItems : memberItems),
    { label: 'Account', to: '/account/password', icon: KeyRound },
]);

const initials = computed(() =>
    (user.value?.name ?? '')
        .split(' ')
        .map((part) => part.charAt(0))
        .filter(Boolean)
        .slice(0, 2)
        .join('')
        .toUpperCase(),
);

const loggingOut = ref(false);

async function onLogout(): Promise<void> {
    loggingOut.value = true;

    try {
        // The store clears local state even if the network call fails.
        await logout();
    } finally {
        loggingOut.value = false;
        emit('close');
        router.push({ name: 'login' });
    }
}
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-40 bg-black/40 md:hidden" @click="emit('close')" />

    <aside
        :class="
            cn(
                'fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-sidebar-border bg-sidebar text-sidebar-foreground transition-transform duration-200 md:static md:z-auto md:translate-x-0',
                open ? 'translate-x-0' : '-translate-x-full',
            )
        "
    >
        <div class="flex items-center gap-2 px-5 py-5">
            <span class="flex size-9 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                <BookMarked class="size-5" />
            </span>
            <span class="text-base font-semibold tracking-tight">{{ APP_NAME }}</span>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-2">
            <RouterLink v-for="item in navItems" :key="item.to" v-slot="{ href, navigate, isActive }" :to="item.to" custom>
                <a
                    :href="href"
                    :class="
                        cn(
                            'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                            isActive
                                ? 'bg-sidebar-accent text-sidebar-accent-foreground'
                                : 'text-sidebar-foreground/70 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground',
                        )
                    "
                    @click="
                        (event: MouseEvent) => {
                            navigate(event);
                            emit('close');
                        }
                    "
                >
                    <component :is="item.icon" class="size-4" />
                    {{ item.label }}
                </a>
            </RouterLink>
        </nav>

        <div class="border-t border-sidebar-border p-3">
            <div class="flex items-center gap-3 rounded-md px-2 py-2">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-sidebar-accent text-sm font-medium">
                    {{ initials }}
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium">{{ user?.name }}</p>
                    <p class="truncate text-xs text-sidebar-foreground/60 capitalize">{{ user?.role }}</p>
                </div>
            </div>
            <Button variant="ghost" class="mt-1 w-full justify-start gap-2" :disabled="loggingOut" @click="onLogout">
                <LogOut class="size-4" />
                {{ loggingOut ? 'Logging out…' : 'Log out' }}
            </Button>
        </div>
    </aside>
</template>
