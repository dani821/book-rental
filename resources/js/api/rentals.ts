import { mapBookResource, type BookResource } from '@/api/books';
import { api } from '@/api/client';
import { mapPaginationMeta, type Paginated, type ResourceObject } from '@/types/api';
import { type Rental, type RentalStatus } from '@/types/rental';

type RentalResource = ResourceObject<
    'rentals',
    {
        status: RentalStatus;
        current_page: number;
        progress_percentage: number;
        extensions_count: number;
        rented_at: string | null;
        due_at: string | null;
        returned_at: string | null;
        created_at: string | null;
        updated_at: string | null;
    }
> & {
    relationships?: { book?: { data?: { id: string; type: string } | null } };
};

interface RentalDocument {
    data: RentalResource;
    included?: BookResource[];
}

interface RentalCollectionDocument {
    data: RentalResource[];
    included?: BookResource[];
    meta: Parameters<typeof mapPaginationMeta>[0];
}

/** The book is exposed as a relationship; `?include=book` returns it in the `included` array. */
const INCLUDE_BOOK = { include: 'book' } as const;

function mapRental(resource: RentalResource, included: BookResource[] | undefined): Rental {
    const bookId = resource.relationships?.book?.data?.id;
    const bookResource = included?.find((candidate) => candidate.id === bookId);

    return {
        id: resource.id,
        status: resource.attributes.status,
        currentPage: resource.attributes.current_page,
        progressPercentage: resource.attributes.progress_percentage,
        extensionsCount: resource.attributes.extensions_count,
        rentedAt: resource.attributes.rented_at,
        dueAt: resource.attributes.due_at,
        returnedAt: resource.attributes.returned_at,
        createdAt: resource.attributes.created_at,
        updatedAt: resource.attributes.updated_at,
        book: bookResource ? mapBookResource(bookResource) : null,
    };
}

export async function listRentals(page = 1): Promise<Paginated<Rental>> {
    const { data } = await api.get<RentalCollectionDocument>('/rentals', { params: { ...INCLUDE_BOOK, page } });

    return { items: data.data.map((rental) => mapRental(rental, data.included)), meta: mapPaginationMeta(data.meta) };
}

export async function extendRental(id: string): Promise<Rental> {
    const { data } = await api.patch<RentalDocument>(`/rentals/${id}/extend`, {}, { params: INCLUDE_BOOK });

    return mapRental(data.data, data.included);
}

export async function updateReadingProgress(id: string, currentPage: number): Promise<Rental> {
    const { data } = await api.patch<RentalDocument>(`/rentals/${id}/progress`, { current_page: currentPage }, { params: INCLUDE_BOOK });

    return mapRental(data.data, data.included);
}

export async function finishRental(id: string): Promise<Rental> {
    const { data } = await api.patch<RentalDocument>(`/rentals/${id}/finish`, {}, { params: INCLUDE_BOOK });

    return mapRental(data.data, data.included);
}
