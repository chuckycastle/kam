import { Router } from 'express';
import { requireAuth, requireMinRole } from '../middleware/auth.js';
import { validateBody, validateParams } from '../middleware/validation.js';
import {
  createCategorySchema,
  updateCategorySchema,
  categoryIdSchema,
} from '../schemas/category.schema.js';
import * as categoryController from '../controllers/category.controller.js';

const router = Router();

// GET /api/categories - List all categories (authenticated)
router.get('/', requireAuth, categoryController.listCategories);

// GET /api/categories/:id - Get single category
router.get(
  '/:id',
  requireAuth,
  validateParams(categoryIdSchema),
  categoryController.getCategory
);

// POST /api/categories - Create category (admin only)
router.post(
  '/',
  requireAuth,
  requireMinRole('ADMIN'),
  validateBody(createCategorySchema),
  categoryController.createCategory
);

// PUT /api/categories/:id - Update category (admin only)
router.put(
  '/:id',
  requireAuth,
  requireMinRole('ADMIN'),
  validateParams(categoryIdSchema),
  validateBody(updateCategorySchema),
  categoryController.updateCategory
);

// DELETE /api/categories/:id - Delete category (admin only)
router.delete(
  '/:id',
  requireAuth,
  requireMinRole('ADMIN'),
  validateParams(categoryIdSchema),
  categoryController.deleteCategory
);

export default router;
