import { z } from 'zod';
import { PAGINATION } from '../config/constants.js';

export const orgIdSchema = z.object({
  id: z.string().uuid('Invalid organization ID'),
});

export const createOrgSchema = z.object({
  name: z.string().min(1, 'Name is required').max(200),
  description: z.string().max(2000).optional(),
  url: z.string().url('Invalid URL').optional().or(z.literal('')),
  contactName: z.string().max(100).optional(),
  contactTitle: z.string().max(100).optional(),
  contactPhone: z.string().max(20).optional(),
  contactEmail: z.string().email('Invalid email').optional().or(z.literal('')),
  categoryId: z.string().uuid('Invalid category ID').optional(),
  assignedToId: z.string().uuid('Invalid user ID').optional(),
});

export const updateOrgSchema = z.object({
  name: z.string().min(1, 'Name is required').max(200).optional(),
  description: z.string().max(2000).optional().nullable(),
  url: z.string().url('Invalid URL').optional().nullable().or(z.literal('')),
  contactName: z.string().max(100).optional().nullable(),
  contactTitle: z.string().max(100).optional().nullable(),
  contactPhone: z.string().max(20).optional().nullable(),
  contactEmail: z.string().email('Invalid email').optional().nullable().or(z.literal('')),
  isAvailable: z.boolean().optional(),
  categoryId: z.string().uuid('Invalid category ID').optional().nullable(),
  assignedToId: z.string().uuid('Invalid user ID').optional().nullable(),
});

export const orgQuerySchema = z.object({
  page: z.coerce.number().int().positive().default(PAGINATION.DEFAULT_PAGE),
  limit: z.coerce
    .number()
    .int()
    .positive()
    .max(PAGINATION.MAX_LIMIT)
    .default(PAGINATION.DEFAULT_LIMIT),
  search: z.string().optional(),
  categoryId: z.string().uuid().optional(),
  assignedToId: z.string().uuid().optional(),
  isAvailable: z
    .string()
    .transform((val) => val === 'true')
    .optional(),
  sortBy: z.enum(['name', 'createdAt', 'updatedAt']).default('name'),
  sortOrder: z.enum(['asc', 'desc']).default('asc'),
});

export const assignOrgSchema = z.object({
  userId: z.string().uuid('Invalid user ID'),
});

export type OrgId = z.infer<typeof orgIdSchema>;
export type CreateOrgInput = z.infer<typeof createOrgSchema>;
export type UpdateOrgInput = z.infer<typeof updateOrgSchema>;
export type OrgQuery = z.infer<typeof orgQuerySchema>;
export type AssignOrgInput = z.infer<typeof assignOrgSchema>;
