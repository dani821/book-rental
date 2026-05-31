import { reactive, ref } from 'vue';

import { isAppError } from '@/types/api';

/**
 * Maps a rejected API error into per-field messages (for inline form display) and a
 * single general message (for an Alert banner). Forms own one instance of this and call
 * `setFromError` in their catch block.
 */
export function useApiError() {
    const fieldErrors = reactive<Record<string, string>>({});
    const generalMessage = ref('');

    function clear(): void {
        for (const key of Object.keys(fieldErrors)) {
            delete fieldErrors[key];
        }
        generalMessage.value = '';
    }

    function clearField(field: string): void {
        delete fieldErrors[field];
    }

    function setFromError(error: unknown): void {
        clear();

        if (isAppError(error)) {
            Object.assign(fieldErrors, error.fields);
            generalMessage.value = error.message;

            return;
        }

        generalMessage.value = 'Something went wrong. Please try again.';
    }

    return { fieldErrors, generalMessage, setFromError, clearField, clear };
}
