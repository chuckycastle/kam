import { Role } from '@prisma/client';
import { ROLE_LEVELS } from '../config/constants.js';

/**
 * Check if user has at least the minimum required role
 */
export function hasMinRole(userRole: Role, requiredRole: Role): boolean {
  return ROLE_LEVELS[userRole] <= ROLE_LEVELS[requiredRole];
}

/**
 * Check if user is an admin (ADMIN or SUPER_ADMIN)
 */
export function isAdmin(userRole: Role): boolean {
  return userRole === Role.ADMIN || userRole === Role.SUPER_ADMIN;
}

/**
 * Check if user is a super admin
 */
export function isSuperAdmin(userRole: Role): boolean {
  return userRole === Role.SUPER_ADMIN;
}

/**
 * Check if user can modify an entity (is owner or admin)
 */
export function canModifyEntity(
  userId: string,
  ownerId: string | null,
  userRole: Role,
  requireOwnerOrAdmin = true
): boolean {
  if (!requireOwnerOrAdmin) return true;

  const isOwner = ownerId === userId;
  const userIsAdmin = isAdmin(userRole);

  return isOwner || userIsAdmin;
}

/**
 * Check if user can view another user's profile
 */
export function canViewProfile(
  currentUserId: string,
  targetUserId: string,
  userRole: Role
): boolean {
  return currentUserId === targetUserId || hasMinRole(userRole, Role.ADMIN);
}

/**
 * Check if user can update another user
 * Returns fields that should be stripped based on permissions
 */
export function filterUserUpdateFields<T extends Record<string, unknown>>(
  data: T,
  _isSelf: boolean,
  userRole: Role
): Partial<T> {
  const filtered = { ...data };

  // Non-admins can't change administrative fields
  if (!isAdmin(userRole)) {
    delete filtered.role;
    delete filtered.isActive;
    delete filtered.supervisorId;
  }

  // Only super admins can assign super admin role
  if (filtered.role === Role.SUPER_ADMIN && !isSuperAdmin(userRole)) {
    delete filtered.role;
  }

  return filtered;
}
