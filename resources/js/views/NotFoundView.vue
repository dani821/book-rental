<script setup lang="ts">
import { computed } from 'vue';

import { buttonVariants } from '@/components/ui/button';
import { useAuth } from '@/composables/useAuth';

const { isAuthenticated } = useAuth();

const homeTarget = computed(() => (isAuthenticated.value ? { name: 'dashboard' } : { name: 'login' }));
</script>

<template>
    <div class="flex min-h-full flex-col items-center justify-center gap-4 px-4 py-12 text-center">
        <p class="text-5xl font-semibold tracking-tight">404</p>
        <p class="text-muted-foreground">We couldn't find the page you were looking for.</p>
        <RouterLink :to="homeTarget" :class="buttonVariants()">Go back home</RouterLink>
    </div>
</template>
