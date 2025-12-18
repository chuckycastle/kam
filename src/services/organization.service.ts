import { prisma } from '../config/database.js';
import { logger } from '../utils/logger.js';
import {
  calculatePagination,
  createPaginationMeta,
  buildSearchCondition,
  type PaginationMeta,
} from './query.service.js';

export interface OrganizationFilters {
  page?: number;
  limit?: number;
  search?: string;
  categoryId?: string;
  assignedToId?: string;
  isAvailable?: boolean;
}

export interface OrganizationListResult {
  organizations: unknown[];
  meta: PaginationMeta;
}

const organizationSelect = {
  id: true,
  name: true,
  description: true,
  url: true,
  contactName: true,
  contactTitle: true,
  contactEmail: true,
  contactPhone: true,
  isAvailable: true,
  createdAt: true,
  category: { select: { id: true, title: true } },
  assignedTo: {
    select: { id: true, username: true, firstName: true, lastName: true },
  },
  _count: { select: { items: true, notes: true } },
};

const organizationDetailSelect = {
  ...organizationSelect,
  items: {
    select: {
      id: true,
      name: true,
      value: true,
      isReceived: true,
    },
    orderBy: { createdAt: 'desc' as const },
  },
  notes: {
    select: {
      id: true,
      content: true,
      createdAt: true,
      author: {
        select: { id: true, username: true, firstName: true, lastName: true },
      },
    },
    orderBy: { createdAt: 'desc' as const },
  },
};

/**
 * List organizations with filters and pagination
 */
export async function listOrganizations(
  filters: OrganizationFilters
): Promise<OrganizationListResult> {
  const { page, limit, skip } = calculatePagination(filters);

  const searchCondition = buildSearchCondition(filters.search, [
    'name',
    'contactName',
  ]);

  const where = {
    ...searchCondition,
    ...(filters.categoryId && { categoryId: filters.categoryId }),
    ...(filters.assignedToId && { assignedToId: filters.assignedToId }),
    ...(filters.isAvailable !== undefined && {
      isAvailable: filters.isAvailable,
    }),
  };

  const [organizations, total] = await Promise.all([
    prisma.organization.findMany({
      where,
      skip,
      take: limit,
      orderBy: { name: 'asc' },
      select: organizationSelect,
    }),
    prisma.organization.count({ where }),
  ]);

  return {
    organizations,
    meta: createPaginationMeta(page, limit, total),
  };
}

/**
 * Get organization by ID with full details
 */
export async function getOrganizationById(id: string) {
  return prisma.organization.findUnique({
    where: { id },
    select: organizationDetailSelect,
  });
}

/**
 * Create a new organization
 */
export async function createOrganization(data: {
  name: string;
  description?: string | null;
  url?: string | null;
  categoryId?: string | null;
  contactName?: string | null;
  contactTitle?: string | null;
  contactEmail?: string | null;
  contactPhone?: string | null;
  assignedToId?: string | null;
}) {
  const organization = await prisma.organization.create({
    data: {
      ...data,
      isAvailable: !data.assignedToId,
    },
    select: organizationSelect,
  });

  logger.info({ orgId: organization.id }, 'Organization created');
  return organization;
}

/**
 * Update an organization
 */
export async function updateOrganization(
  id: string,
  data: Partial<{
    name: string;
    description: string | null;
    url: string | null;
    categoryId: string | null;
    contactName: string | null;
    contactTitle: string | null;
    contactEmail: string | null;
    contactPhone: string | null;
    assignedToId: string | null;
    isAvailable: boolean;
  }>
) {
  const organization = await prisma.organization.update({
    where: { id },
    data,
    select: organizationSelect,
  });

  logger.info({ orgId: id }, 'Organization updated');
  return organization;
}

/**
 * Delete an organization
 */
export async function deleteOrganization(id: string): Promise<void> {
  await prisma.organization.delete({ where: { id } });
  logger.info({ orgId: id }, 'Organization deleted');
}

/**
 * Assign organization to a user
 */
export async function assignOrganization(id: string, userId: string) {
  const organization = await prisma.organization.update({
    where: { id },
    data: {
      assignedToId: userId,
      isAvailable: false,
    },
    select: organizationSelect,
  });

  logger.info({ orgId: id, userId }, 'Organization assigned');
  return organization;
}

/**
 * Check if organization exists
 */
export async function organizationExists(id: string): Promise<boolean> {
  const count = await prisma.organization.count({ where: { id } });
  return count > 0;
}

/**
 * Get organization items
 */
export async function getOrganizationItems(id: string) {
  return prisma.item.findMany({
    where: { organizationId: id },
    orderBy: { createdAt: 'desc' },
    select: {
      id: true,
      name: true,
      description: true,
      value: true,
      quantity: true,
      isReceived: true,
      createdAt: true,
    },
  });
}

/**
 * Get organization notes
 */
export async function getOrganizationNotes(id: string) {
  return prisma.note.findMany({
    where: { organizationId: id },
    orderBy: { createdAt: 'desc' },
    select: {
      id: true,
      content: true,
      createdAt: true,
      author: {
        select: { id: true, username: true, firstName: true, lastName: true },
      },
    },
  });
}
