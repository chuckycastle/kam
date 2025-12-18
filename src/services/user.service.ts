import { Role } from '@prisma/client';
import { prisma } from '../config/database.js';
import { logger } from '../utils/logger.js';
import {
  calculatePagination,
  createPaginationMeta,
  buildSearchCondition,
  type PaginationMeta,
} from './query.service.js';

export interface UserFilters {
  page?: number;
  limit?: number;
  search?: string;
  role?: string;
  isActive?: boolean;
  sortBy?: string;
  sortOrder?: 'asc' | 'desc';
}

export interface UserListResult {
  users: unknown[];
  meta: PaginationMeta;
}

const userListSelect = {
  id: true,
  email: true,
  username: true,
  firstName: true,
  lastName: true,
  role: true,
  isActive: true,
  createdAt: true,
  _count: { select: { organizations: true, items: true } },
};

const userDetailSelect = {
  id: true,
  email: true,
  username: true,
  firstName: true,
  lastName: true,
  role: true,
  isActive: true,
  createdAt: true,
  updatedAt: true,
  supervisor: {
    select: { id: true, username: true, firstName: true, lastName: true },
  },
  _count: { select: { organizations: true, items: true, notes: true } },
};

/**
 * List users with filters and pagination
 */
export async function listUsers(filters: UserFilters): Promise<UserListResult> {
  const { page, limit, skip } = calculatePagination(filters);

  const searchCondition = buildSearchCondition(filters.search, [
    'username',
    'email',
    'firstName',
    'lastName',
  ]);

  const where = {
    ...searchCondition,
    ...(filters.role && { role: filters.role as Role }),
    ...(filters.isActive !== undefined && { isActive: filters.isActive }),
  };

  const sortBy = filters.sortBy || 'username';
  const sortOrder = filters.sortOrder || 'asc';

  const [users, total] = await Promise.all([
    prisma.user.findMany({
      where,
      skip,
      take: limit,
      orderBy: { [sortBy]: sortOrder },
      select: userListSelect,
    }),
    prisma.user.count({ where }),
  ]);

  return {
    users,
    meta: createPaginationMeta(page, limit, total),
  };
}

/**
 * Get user by ID with details
 */
export async function getUserById(id: string) {
  return prisma.user.findUnique({
    where: { id },
    select: userDetailSelect,
  });
}

/**
 * Update a user
 */
export async function updateUser(
  id: string,
  data: Partial<{
    email: string;
    username: string;
    firstName: string;
    lastName: string;
    role: Role;
    isActive: boolean;
    supervisorId: string | null;
  }>
) {
  const user = await prisma.user.update({
    where: { id },
    data,
    select: {
      id: true,
      email: true,
      username: true,
      firstName: true,
      lastName: true,
      role: true,
      isActive: true,
      updatedAt: true,
    },
  });

  logger.info({ userId: id }, 'User updated');
  return user;
}

/**
 * Soft delete (deactivate) a user
 */
export async function deactivateUser(
  id: string,
  deletedById: string
): Promise<void> {
  await prisma.user.update({
    where: { id },
    data: { isActive: false },
  });

  logger.info({ userId: id, deletedBy: deletedById }, 'User deactivated');
}

/**
 * Get user's assigned organizations
 */
export async function getUserOrganizations(id: string) {
  return prisma.organization.findMany({
    where: { assignedToId: id },
    orderBy: { name: 'asc' },
    include: {
      category: true,
      _count: { select: { items: true, notes: true } },
    },
  });
}

/**
 * Check if user exists
 */
export async function userExists(id: string): Promise<boolean> {
  const count = await prisma.user.count({ where: { id } });
  return count > 0;
}
