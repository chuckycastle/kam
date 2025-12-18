import { z } from 'zod';
import { PAGINATION } from '../config/constants.js';

export const itemIdSchema = z.object({
  id: z.string().uuid('Invalid item ID'),
});

export const createItemSchema = z.object({
  name: z.string().min(1, 'Name is required').max(200),
  description: z.string().max(2000).optional(),
  value: z.coerce.number().positive('Value must be positive').optional(),
  quantity: z.coerce.number().int().positive().default(1),
  organizationId: z.string().uuid('Invalid organization ID'),
});

export const updateItemSchema = z.object({
  name: z.string().min(1, 'Name is required').max(200).optional(),
  description: z.string().max(2000).optional().nullable(),
  value: z.coerce
    .number()
    .positive('Value must be positive')
    .optional()
    .nullable(),
  quantity: z.coerce.number().int().positive().optional(),
  isReceived: z.boolean().optional(),
});

export const itemQuerySchema = z.object({
  page: z.coerce.number().int().positive().default(PAGINATION.DEFAULT_PAGE),
  limit: z.coerce
    .number()
    .int()
    .positive()
    .max(PAGINATION.MAX_LIMIT)
    .default(PAGINATION.DEFAULT_LIMIT),
  search: z.string().optional(),
  organizationId: z.string().uuid().optional(),
  createdById: z.string().uuid().optional(),
  isReceived: z
    .string()
    .transform((val) => val === 'true')
    .optional(),
  sortBy: z
    .enum(['name', 'value', 'createdAt', 'updatedAt'])
    .default('createdAt'),
  sortOrder: z.enum(['asc', 'desc']).default('desc'),
});

export type ItemId = z.infer<typeof itemIdSchema>;
export type CreateItemInput = z.infer<typeof createItemSchema>;
export type UpdateItemInput = z.infer<typeof updateItemSchema>;
export type ItemQuery = z.infer<typeof itemQuerySchema>;
