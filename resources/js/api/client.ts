import axios, { AxiosError, type AxiosInstance } from 'axios';

import { type AppError, type JsonApiErrorDocument } from '@/types/api';
import { clearToken, getToken } from '@/api/token';

const JSON_API_MEDIA_TYPE = 'application/vnd.api+json';

export const api: AxiosInstance = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL,
    headers: {
        Accept: JSON_API_MEDIA_TYPE,
        'Content-Type': JSON_API_MEDIA_TYPE,
    },
});

/**
 * Called when the API rejects a request with 401. Registered by the app (see main.ts)
 * so the client can trigger a session reset + redirect without importing the store/router.
 */
let unauthorizedHandler: (() => void) | null = null;

export function registerUnauthorizedHandler(handler: () => void): void {
    unauthorizedHandler = handler;
}

api.interceptors.request.use((config) => {
    const token = getToken();

    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
});

api.interceptors.response.use(
    (response) => response,
    (error: AxiosError) => {
        const appError = normalizeError(error);

        if (appError.status === 401) {
            clearToken();
            unauthorizedHandler?.();
        }

        return Promise.reject(appError);
    },
);

const DEFAULT_MESSAGES: Record<number, string> = {
    401: 'Your session has expired. Please sign in again.',
    403: 'You are not allowed to perform this action.',
    404: 'The requested resource could not be found.',
    409: 'This action conflicts with the current state. Please refresh and try again.',
    422: 'Please correct the highlighted fields and try again.',
};

function defaultMessageFor(status: number): string {
    if (DEFAULT_MESSAGES[status]) {
        return DEFAULT_MESSAGES[status];
    }

    if (status >= 500) {
        return 'Something went wrong on our end. Please try again in a moment.';
    }

    return 'Something went wrong. Please try again.';
}

/**
 * Map the field name out of a JSON:API pointer. Handles both the bare form this API
 * emits (`/email`) and the spec's nested form (`/data/attributes/email`) by taking the
 * last path segment.
 */
function fieldFromPointer(pointer: string): string | null {
    const segments = pointer.split('/').filter(Boolean);

    return segments.length > 0 ? segments[segments.length - 1] : null;
}

function normalizeError(error: AxiosError): AppError {
    if (!error.response) {
        return {
            status: 0,
            message: 'Unable to reach the server. Check your connection and try again.',
            fields: {},
        };
    }

    const { status, data, headers } = error.response;
    const fields: Record<string, string> = {};
    const document = data as Partial<JsonApiErrorDocument> | null;
    let generalMessage = '';

    if (document && Array.isArray(document.errors)) {
        for (const item of document.errors) {
            const pointer = item.source?.pointer;
            const field = pointer ? fieldFromPointer(pointer) : null;
            const detail = item.detail ?? item.title ?? 'Invalid value.';

            if (field) {
                if (!(field in fields)) {
                    fields[field] = detail;
                }
            } else if (!generalMessage) {
                generalMessage = detail;
            }
        }
    }

    // Only surface a banner message when there is no field-level error to show inline,
    // so a 422 doesn't duplicate the same text in both places.
    let message = generalMessage;

    if (!message) {
        message = Object.keys(fields).length > 0 ? '' : defaultMessageFor(status);
    }

    if (status === 429) {
        const retryAfter = Number(extractHeader(headers, 'retry-after'));

        if (Number.isFinite(retryAfter) && retryAfter > 0) {
            return {
                status,
                message: `Too many attempts. Please try again in ${retryAfter} second${retryAfter === 1 ? '' : 's'}.`,
                fields,
            };
        }

        return {
            status,
            message: 'Too many attempts. Please wait a moment and try again.',
            fields,
        };
    }

    return { status, message, fields };
}

function extractHeader(headers: unknown, name: string): string | undefined {
    if (headers && typeof headers === 'object' && name in headers) {
        const value = (headers as Record<string, unknown>)[name];

        return typeof value === 'string' ? value : undefined;
    }

    return undefined;
}
