export interface ResourceObject<TType extends string, TAttributes> {
    id: string;
    type: TType;
    attributes: TAttributes;
}

export interface ResourceResponse<TResource, TMeta = undefined> {
    data: TResource;
    meta?: TMeta;
}

export interface PaginationMeta {
    currentPage: number;
    lastPage: number;
    perPage: number;
    total: number;
    from: number | null;
    to: number | null;
}

export interface Paginated<T> {
    items: T[];
    meta: PaginationMeta;
}

export function mapPaginationMeta(meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}): PaginationMeta {
    return {
        currentPage: meta.current_page,
        lastPage: meta.last_page,
        perPage: meta.per_page,
        total: meta.total,
        from: meta.from,
        to: meta.to,
    };
}

export interface JsonApiError {
    status?: string;
    title?: string;
    detail?: string;
    source?: {
        pointer?: string;
        parameter?: string;
    };
}

export interface JsonApiErrorDocument {
    errors: JsonApiError[];
}

export interface AppError {
    status: number;
    message: string;
    fields: Record<string, string>;
}

export function isAppError(value: unknown): value is AppError {
    return typeof value === 'object' && value !== null && 'status' in value && 'fields' in value && 'message' in value;
}
