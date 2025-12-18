import { z } from 'zod';

export const categoryIdSchema = z.object({
  id: z.string().uuid('Invalid category ID'),
});

export const createCategorySchema = z.object({
  title: z.string().min(1, 'Category name is required').max(100),
  description: z.string().max(500).optional().nullable(),
});

export const updateCategorySchema = z.object({
  title: z.string().min(1, 'Category name is required').max(100).optional(),
  description: z.string().max(500).optional().nullable(),
});

export type CategoryId = z.infer<typeof categoryIdSchema>;
export type CreateCategoryInput = z.infer<typeof createCategorySchema>;
export type UpdateCategoryInput = z.infer<typeof updateCategorySchema>;
