/**
 * Single source of truth for the persisted Sanctum bearer token. The axios client
 * reads it on every request; the auth store writes it on login/logout. Kept as a
 * standalone module so neither the client nor the store has to import the other.
 */

const TOKEN_KEY = 'book_rental_token';

export function getToken(): string | null {
    return localStorage.getItem(TOKEN_KEY);
}

export function setToken(token: string): void {
    localStorage.setItem(TOKEN_KEY, token);
}

export function clearToken(): void {
    localStorage.removeItem(TOKEN_KEY);
}
