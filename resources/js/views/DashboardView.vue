<script setup lang="ts">
import { computed, type FunctionalComponent } from 'vue';
import { RouterLink } from 'vue-router';
import { ArrowRight, BookOpen, LibraryBig, ShieldCheck, Users } from '@lucide/vue';

import AppShell from '@/components/layout/AppShell.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useAuth } from '@/composables/useAuth';

const { user, isAdmin } = useAuth();

interface QuickLink {
    label: string;
    description: string;
    to: string;
    icon: FunctionalComponent;
}

const quickLinks = computed<QuickLink[]>(() =>
    isAdmin.value
        ? [
              { label: 'Users', description: 'Manage member accounts.', to: '/users', icon: Users },
              { label: 'Books', description: 'Curate the catalogue.', to: '/books', icon: BookOpen },
          ]
        : [
              { label: 'Books', description: 'Browse available titles.', to: '/books', icon: BookOpen },
              { label: 'Rentals', description: 'Your active rentals.', to: '/rentals', icon: LibraryBig },
          ],
);
</script>

<template>
    <AppShell>
        <div class="space-y-8">
            <header class="space-y-1">
                <h1 class="text-2xl font-semibold tracking-tight">Welcome back, {{ user?.name }}</h1>
                <p class="text-sm text-muted-foreground">Here's what you can do with your account today.</p>
            </header>

            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Account</CardTitle>
                    <CardDescription>Your current profile details.</CardDescription>
                </CardHeader>
                <CardContent class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground uppercase">Name</p>
                        <p class="mt-1 text-sm">{{ user?.name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-muted-foreground uppercase">Email</p>
                        <p class="mt-1 text-sm">{{ user?.email }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-muted-foreground uppercase">Role</p>
                        <p class="mt-1 inline-flex items-center gap-1.5 text-sm capitalize">
                            <ShieldCheck class="size-4 text-muted-foreground" />
                            {{ user?.role }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <section class="space-y-3">
                <h2 class="text-sm font-medium text-muted-foreground">Quick links</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <RouterLink
                        v-for="link in quickLinks"
                        :key="link.to"
                        :to="link.to"
                        class="group rounded-xl border bg-card p-5 text-card-foreground shadow-sm transition-colors hover:border-foreground/20 hover:bg-accent"
                    >
                        <div class="flex items-start justify-between">
                            <span class="flex size-10 items-center justify-center rounded-lg bg-secondary text-secondary-foreground">
                                <component :is="link.icon" class="size-5" />
                            </span>
                            <ArrowRight class="size-4 text-muted-foreground transition-transform group-hover:translate-x-0.5" />
                        </div>
                        <p class="mt-4 font-medium">{{ link.label }}</p>
                        <p class="text-sm text-muted-foreground">{{ link.description }}</p>
                    </RouterLink>
                </div>
            </section>
        </div>
    </AppShell>
</template>
