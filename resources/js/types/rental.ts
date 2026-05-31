import { type Book } from '@/types/book';

export type RentalStatus = 'active' | 'completed';

/** Backend rule (App\Models\BookRental::MAX_EXTENSIONS): a rental can be extended at most twice. */
export const MAX_RENTAL_EXTENSIONS = 2;

export interface Rental {
    id: string;
    status: RentalStatus;
    currentPage: number;
    progressPercentage: number;
    extensionsCount: number;
    rentedAt: string | null;
    dueAt: string | null;
    returnedAt: string | null;
    createdAt: string | null;
    updatedAt: string | null;
    book: Book | null;
}
