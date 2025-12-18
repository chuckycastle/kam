import { PAGINATION } from '../config/constants.js';

export interface PaginationParams {
  page?: number;
  limit?: number;
}

export interface PaginationResult {
  page: number;
  limit: number;
  skip: number;
}

export interface PaginationMeta {
  page: number;
  limit: number;
  total: number;
  totalPages: number;
}

/**
 * Calculate pagination values from request params
 */
export function calculatePagination(
  params: PaginationParams
): PaginationResult {
  const page = Math.max(1, params.page || PAGINATION.DEFAULT_PAGE);
  const limit = Math.min(
    Math.max(1, params.limit || PAGINATION.DEFAULT_LIMIT),
    PAGINATION.MAX_LIMIT
  );
  const skip = (page - 1) * limit;

  return { page, limit, skip };
}

/**
 * Create pagination metadata for response
 */
export function createPaginationMeta(
  page: number,
  limit: number,
  total: number
): PaginationMeta {
  return {
    page,
    limit,
    total,
    totalPages: Math.ceil(total / limit),
  };
}

/**
 * Build search OR conditions for multiple fields
 */
export function buildSearchCondition(
  search: string | undefined,
  fields: string[]
): object | undefined {
  if (!search) return undefined;

  return {
    OR: fields.map((field) => ({
      [field]: { contains: search, mode: 'insensitive' as const },
    })),
  };
}

/**
 * Build where clause with optional filters
 */
export function buildWhereClause(
  filters: Record<string, unknown>
): Record<string, unknown> {
  const where: Record<string, unknown> = {};

  for (const [key, value] of Object.entries(filters)) {
    if (value !== undefined && value !== null && value !== '') {
      where[key] = value;
    }
  }

  return where;
}

/**
 * Parse boolean query param
 */
export function parseBooleanParam(
  value: string | undefined
): boolean | undefined {
  if (value === 'true') return true;
  if (value === 'false') return false;
  return undefined;
}
