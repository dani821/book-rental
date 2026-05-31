<script setup lang="ts">
import { ref } from 'vue';
import { Menu } from '@lucide/vue';

import AppSidebar from '@/components/layout/AppSidebar.vue';
import { Button } from '@/components/ui/button';
import { APP_NAME } from '@/lib/constants';

const sidebarOpen = ref(false);
</script>

<template>
    <div class="flex h-dvh w-full flex-col overflow-hidden md:flex-row">
        <AppSidebar :open="sidebarOpen" @close="sidebarOpen = false" />

        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
            <header class="flex items-center justify-between border-b px-4 py-3 md:hidden">
                <span class="text-base font-semibold tracking-tight">{{ APP_NAME }}</span>
                <Button variant="ghost" size="icon" aria-label="Open menu" title="Open menu" @click="sidebarOpen = true">
                    <Menu class="size-5" />
                </Button>
            </header>

            <main class="flex-1 overflow-y-auto px-4 py-6 md:px-8 md:py-10">
                <div class="mx-auto w-full max-w-5xl">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
