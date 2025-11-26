import { z } from 'zod';
import { PAGINATION, ROLES } from '../config/constants.js';

export const userIdSchema = z.object({
  id: z.string().uuid('Invalid user ID'),
});

export const updateUserSchema = z.object({
  email: z.string().email('Invalid email').optional(),
  username: z
    .string()
    .min(3, 'Username must be at least 3 characters')
    .max(30, 'Username must be at most 30 characters')
    .regex(/^[a-zA-Z0-9_]+$/, 'Username can only contain letters, numbers, and underscores')
    .optional(),
  firstName: z.string().min(1).max(50).optional(),
  lastName: z.string().min(1).max(50).optional(),
  role: z.enum([
    ROLES.SUPER_ADMIN,
    ROLES.ADMIN,
    ROLES.COORDINATOR,
    ROLES.VOLUNTEER,
    ROLES.PUBLIC,
  ]).optional(),
  isActive: z.boolean().optional(),
  supervisorId: z.string().uuid('Invalid supervisor ID').optional().nullable(),
});

export const userQuerySchema = z.object({
  page: z.coerce.number().int().positive().default(PAGINATION.DEFAULT_PAGE),
  limit: z.coerce
    .number()
    .int()
    .positive()
    .max(PAGINATION.MAX_LIMIT)
    .default(PAGINATION.DEFAULT_LIMIT),
  search: z.string().optional(),
  role: z.enum([
    ROLES.SUPER_ADMIN,
    ROLES.ADMIN,
    ROLES.COORDINATOR,
    ROLES.VOLUNTEER,
    ROLES.PUBLIC,
  ]).optional(),
  isActive: z
    .string()
    .transform((val) => val === 'true')
    .optional(),
  sortBy: z.enum(['username', 'email', 'firstName', 'lastName', 'createdAt']).default('username'),
  sortOrder: z.enum(['asc', 'desc']).default('asc'),
});

export type UserId = z.infer<typeof userIdSchema>;
export type UpdateUserInput = z.infer<typeof updateUserSchema>;
export type UserQuery = z.infer<typeof userQuerySchema>;
