<script setup lang="ts">
import { nextTick, onBeforeUnmount, ref, watch, type HTMLAttributes } from 'vue';
import { X } from '@lucide/vue';

import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        title?: string;
        description?: string;
        class?: HTMLAttributes['class'];
    }>(),
    {
        title: undefined,
        description: undefined,
        class: undefined,
    },
);

const open = defineModel<boolean>('open', { required: true });
const panel = ref<HTMLElement | null>(null);

/** Elements that can receive keyboard focus, used to keep Tab cycling inside the dialog. */
const FOCUSABLE_SELECTOR =
    'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';

/** The element focused before the dialog opened, so focus can be returned there on close. */
let previouslyFocused: HTMLElement | null = null;

function close(): void {
    open.value = false;
}

function focusableElements(): HTMLElement[] {
    return panel.value ? Array.from(panel.value.querySelectorAll<HTMLElement>(FOCUSABLE_SELECTOR)) : [];
}

/** Keep Tab / Shift+Tab within the dialog so focus can't drift to the page behind it. */
function trapTab(event: KeyboardEvent): void {
    const focusable = focusableElements();

    if (focusable.length === 0) {
        event.preventDefault();
        panel.value?.focus();

        return;
    }

    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    const active = document.activeElement;

    if (event.shiftKey && (active === first || active === panel.value)) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && active === last) {
        event.preventDefault();
        first.focus();
    }
}

function onKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape') {
        close();
    } else if (event.key === 'Tab') {
        trapTab(event);
    }
}

watch(
    open,
    (isOpen) => {
        if (isOpen) {
            previouslyFocused = document.activeElement instanceof HTMLElement ? document.activeElement : null;
            document.addEventListener('keydown', onKeydown);
            document.body.style.overflow = 'hidden';
            void nextTick(() => panel.value?.focus());
        } else {
            document.removeEventListener('keydown', onKeydown);
            document.body.style.overflow = '';
            previouslyFocused?.focus();
            previouslyFocused = null;
        }
    },
    { immediate: true },
);

onBeforeUnmount(() => {
    document.removeEventListener('keydown', onKeydown);
    document.body.style.overflow = '';
});
</script>

<template>
    <Teleport to="body">
        <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" @click="close" />
            <div
                ref="panel"
                role="dialog"
                aria-modal="true"
                tabindex="-1"
                :class="
                    cn(
                        'relative z-10 max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl border bg-card text-card-foreground shadow-lg outline-none',
                        props.class,
                    )
                "
            >
                <div class="flex items-start justify-between gap-4 p-6 pb-4">
                    <div class="space-y-1">
                        <h2 v-if="title" class="text-lg font-semibold tracking-tight">{{ title }}</h2>
                        <p v-if="description" class="text-sm text-muted-foreground">{{ description }}</p>
                    </div>
                    <Button variant="ghost" size="icon" class="-mt-1 -mr-2 shrink-0" aria-label="Close" title="Close" @click="close">
                        <X class="size-4" />
                    </Button>
                </div>

                <div class="px-6 pb-6">
                    <slot />
                </div>

                <div v-if="$slots.footer" class="flex justify-end gap-2 border-t px-6 py-4">
                    <slot name="footer" />
                </div>
            </div>
        </div>
    </Teleport>
</template>
