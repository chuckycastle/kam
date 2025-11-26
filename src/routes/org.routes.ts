import { Router } from 'express';
import { requireAuth, requireMinRole } from '../middleware/auth.js';
import { validateBody, validateParams } from '../middleware/validation.js';
import {
  createOrgSchema,
  updateOrgSchema,
  orgIdSchema,
} from '../schemas/org.schema.js';
import * as orgController from '../controllers/org.controller.js';

const router = Router();

// GET /api/orgs - List organizations
router.get('/', orgController.listOrganizations);

// GET /api/orgs/:id - Get single organization
router.get('/:id', validateParams(orgIdSchema), orgController.getOrganization);

// POST /api/orgs - Create organization (requires COORDINATOR+)
router.post(
  '/',
  requireAuth,
  requireMinRole('COORDINATOR'),
  validateBody(createOrgSchema),
  orgController.createOrganization
);

// PUT /api/orgs/:id - Update organization (requires COORDINATOR+)
router.put(
  '/:id',
  requireAuth,
  requireMinRole('COORDINATOR'),
  validateParams(orgIdSchema),
  validateBody(updateOrgSchema),
  orgController.updateOrganization
);

// DELETE /api/orgs/:id - Delete organization (requires ADMIN+)
router.delete(
  '/:id',
  requireAuth,
  requireMinRole('ADMIN'),
  validateParams(orgIdSchema),
  orgController.deleteOrganization
);

// POST /api/orgs/:id/assign - Assign organization to user
router.post(
  '/:id/assign',
  requireAuth,
  requireMinRole('COORDINATOR'),
  validateParams(orgIdSchema),
  orgController.assignOrganization
);

// GET /api/orgs/:id/items - Get items for organization
router.get(
  '/:id/items',
  validateParams(orgIdSchema),
  orgController.getOrganizationItems
);

// GET /api/orgs/:id/notes - Get notes for organization
router.get(
  '/:id/notes',
  requireAuth,
  validateParams(orgIdSchema),
  orgController.getOrganizationNotes
);

export default router;
