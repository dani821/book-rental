import { api } from '@/api/client';
import { mapPaginationMeta, type Paginated, type ResourceObject, type ResourceResponse } from '@/types/api';
import { type Book, type BookFormPayload, type BookGenre, type BookListParams } from '@/types/book';

export type BookResource = ResourceObject<
    'books',
    {
        title: string;
        author: string;
        genre: BookGenre;
        isbn: string | null;
        published_year: number | null;
        total_pages: number;
        total_copies: number;
        available_copies: number;
        is_rented_by_current_user?: boolean;
        created_at: string | null;
        updated_at: string | null;
        deleted_at?: string | null;
    }
>;

interface PaginatedBooksResponse {
    data: BookResource[];
    meta: Parameters<typeof mapPaginationMeta>[0];
}

/** Map a `books` JSON:API resource to the client {@link Book}. Exported for reuse (e.g. rentals' included books). */
export function mapBookResource(resource: BookResource): Book {
    return {
        id: resource.id,
        title: resource.attributes.title,
        author: resource.attributes.author,
        genre: resource.attributes.genre,
        isbn: resource.attributes.isbn,
        publishedYear: resource.attributes.published_year,
        totalPages: resource.attributes.total_pages,
        totalCopies: resource.attributes.total_copies,
        availableCopies: resource.attributes.available_copies,
        rentedByCurrentUser: resource.attributes.is_rented_by_current_user ?? false,
        createdAt: resource.attributes.created_at,
        updatedAt: resource.attributes.updated_at,
        deletedAt: resource.attributes.deleted_at ?? null,
    };
}

/** Build the flat, bracketed query the JSON:API/Spatie QueryBuilder backend expects. */
function toQuery(params: BookListParams): Record<string, string | number> {
    const query: Record<string, string | number> = { page: params.page, sort: params.sort };

    const search = params.search.trim();
    if (search !== '') {
        query[`filter[${params.searchField}]`] = search;
    }

    if (params.genre) {
        query['filter[genre]'] = params.genre;
    }

    if (params.availableOnly) {
        query['filter[available]'] = 1;
    }

    return query;
}

export async function listBooks(params: BookListParams): Promise<Paginated<Book>> {
    const { data } = await api.get<PaginatedBooksResponse>('/books', { params: toQuery(params) });

    return { items: data.data.map(mapBookResource), meta: mapPaginationMeta(data.meta) };
}

export async function getBook(id: string): Promise<Book> {
    const { data } = await api.get<ResourceResponse<BookResource>>(`/books/${id}`);

    return mapBookResource(data.data);
}

export async function createBook(payload: BookFormPayload): Promise<Book> {
    const { data } = await api.post<ResourceResponse<BookResource>>('/books', payload);

    return mapBookResource(data.data);
}

export async function updateBook(id: string, payload: Partial<BookFormPayload>): Promise<Book> {
    const { data } = await api.patch<ResourceResponse<BookResource>>(`/books/${id}`, payload);

    return mapBookResource(data.data);
}

export async function deleteBook(id: string): Promise<void> {
    await api.delete(`/books/${id}`);
}

export async function rentBook(bookId: string): Promise<void> {
    await api.post(`/books/${bookId}/rentals`);
}
