import { Router } from 'express';
import { requireAuth, requireMinRole } from '../middleware/auth.js';
import { validateBody, validateParams } from '../middleware/validation.js';
import { updateUserSchema, userIdSchema } from '../schemas/user.schema.js';
import * as userController from '../controllers/user.controller.js';

const router = Router();

// GET /api/users - List users (requires ADMIN+)
router.get('/', requireAuth, requireMinRole('ADMIN'), userController.listUsers);

// GET /api/users/:id - Get single user
router.get(
  '/:id',
  requireAuth,
  validateParams(userIdSchema),
  userController.getUser
);

// PUT /api/users/:id - Update user
router.put(
  '/:id',
  requireAuth,
  validateParams(userIdSchema),
  validateBody(updateUserSchema),
  userController.updateUser
);

// DELETE /api/users/:id - Delete user (requires SUPER_ADMIN)
router.delete(
  '/:id',
  requireAuth,
  requireMinRole('SUPER_ADMIN'),
  validateParams(userIdSchema),
  userController.deleteUser
);

// GET /api/users/:id/organizations - Get user's assigned organizations
router.get(
  '/:id/organizations',
  requireAuth,
  validateParams(userIdSchema),
  userController.getUserOrganizations
);

export default router;
