export type BookGenre = 'fiction' | 'non_fiction' | 'science' | 'history' | 'fantasy' | 'biography';

export const BOOK_GENRES: ReadonlyArray<{ value: BookGenre; label: string }> = [
    { value: 'fiction', label: 'Fiction' },
    { value: 'non_fiction', label: 'Non-fiction' },
    { value: 'science', label: 'Science' },
    { value: 'history', label: 'History' },
    { value: 'fantasy', label: 'Fantasy' },
    { value: 'biography', label: 'Biography' },
];

export function genreLabel(genre: BookGenre): string {
    return BOOK_GENRES.find((option) => option.value === genre)?.label ?? genre;
}

export interface Book {
    id: string;
    title: string;
    author: string;
    genre: BookGenre;
    isbn: string | null;
    publishedYear: number | null;
    totalPages: number;
    totalCopies: number;
    availableCopies: number;
    rentedByCurrentUser: boolean;
    createdAt: string | null;
    updatedAt: string | null;
    deletedAt: string | null;
}

export const BOOK_SORTS: ReadonlyArray<{ value: string; label: string }> = [
    { value: 'title', label: 'Title (A–Z)' },
    { value: '-title', label: 'Title (Z–A)' },
    { value: 'author', label: 'Author (A–Z)' },
    { value: '-author', label: 'Author (Z–A)' },
    { value: '-published_year', label: 'Year published (newest)' },
    { value: 'published_year', label: 'Year published (oldest)' },
];

export type BookSearchField = 'title' | 'author';

export interface BookListParams {
    page: number;
    search: string;
    searchField: BookSearchField;
    genre: BookGenre | null;
    availableOnly: boolean;
    sort: string;
}

export interface BookFormPayload {
    title: string;
    author: string;
    genre: BookGenre;
    isbn: string | null;
    published_year: number | null;
    total_pages: number;
    total_copies: number;
}
