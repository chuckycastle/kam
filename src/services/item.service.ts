import { prisma } from '../config/database.js';
import { logger } from '../utils/logger.js';
import {
  calculatePagination,
  createPaginationMeta,
  buildSearchCondition,
  type PaginationMeta,
} from './query.service.js';

export interface ItemFilters {
  page?: number;
  limit?: number;
  search?: string;
  organizationId?: string;
  createdById?: string;
  isReceived?: boolean;
}

export interface ItemListResult {
  items: unknown[];
  meta: PaginationMeta;
}

const itemSelect = {
  id: true,
  name: true,
  description: true,
  value: true,
  quantity: true,
  isReceived: true,
  receivedAt: true,
  createdAt: true,
  organization: {
    select: { id: true, name: true },
  },
  createdBy: {
    select: { id: true, username: true, firstName: true, lastName: true },
  },
};

const itemDetailSelect = {
  ...itemSelect,
  organization: {
    select: {
      id: true,
      name: true,
      contactName: true,
      contactEmail: true,
      contactPhone: true,
    },
  },
};

/**
 * List items with filters and pagination
 */
export async function listItems(filters: ItemFilters): Promise<ItemListResult> {
  const { page, limit, skip } = calculatePagination(filters);

  const searchCondition = buildSearchCondition(filters.search, [
    'name',
    'description',
  ]);

  const where = {
    ...searchCondition,
    ...(filters.organizationId && { organizationId: filters.organizationId }),
    ...(filters.createdById && { createdById: filters.createdById }),
    ...(filters.isReceived !== undefined && { isReceived: filters.isReceived }),
  };

  const [items, total] = await Promise.all([
    prisma.item.findMany({
      where,
      skip,
      take: limit,
      orderBy: { createdAt: 'desc' },
      select: itemSelect,
    }),
    prisma.item.count({ where }),
  ]);

  return {
    items,
    meta: createPaginationMeta(page, limit, total),
  };
}

/**
 * Get item by ID with details
 */
export async function getItemById(id: string) {
  return prisma.item.findUnique({
    where: { id },
    select: itemDetailSelect,
  });
}

/**
 * Create a new item
 */
export async function createItem(data: {
  name: string;
  description?: string | null;
  value?: number | null;
  quantity?: number;
  organizationId: string;
  createdById: string;
}) {
  const item = await prisma.item.create({
    data: {
      name: data.name,
      description: data.description,
      value: data.value,
      quantity: data.quantity || 1,
      organizationId: data.organizationId,
      createdById: data.createdById,
    },
    select: itemSelect,
  });

  logger.info({ itemId: item.id, orgId: data.organizationId }, 'Item created');
  return item;
}

/**
 * Update an item
 */
export async function updateItem(
  id: string,
  data: Partial<{
    name: string;
    description: string | null;
    value: number | null;
    quantity: number;
    organizationId: string;
  }>
) {
  const item = await prisma.item.update({
    where: { id },
    data,
    select: itemSelect,
  });

  logger.info({ itemId: id }, 'Item updated');
  return item;
}

/**
 * Delete an item
 */
export async function deleteItem(id: string): Promise<void> {
  await prisma.item.delete({ where: { id } });
  logger.info({ itemId: id }, 'Item deleted');
}

/**
 * Mark item as received
 */
export async function markItemReceived(id: string) {
  const item = await prisma.item.update({
    where: { id },
    data: {
      isReceived: true,
      receivedAt: new Date(),
    },
    select: itemSelect,
  });

  logger.info({ itemId: id }, 'Item marked as received');
  // TODO: Send email notification
  return item;
}

/**
 * Check if item exists and return creator ID
 */
export async function getItemOwnership(
  id: string
): Promise<{ exists: boolean; createdById: string | null }> {
  const item = await prisma.item.findUnique({
    where: { id },
    select: { createdById: true },
  });

  return {
    exists: !!item,
    createdById: item?.createdById || null,
  };
}
