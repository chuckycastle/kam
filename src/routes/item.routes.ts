import { Router } from 'express';
import { requireAuth, requireMinRole } from '../middleware/auth.js';
import { validateBody, validateParams } from '../middleware/validation.js';
import {
  createItemSchema,
  updateItemSchema,
  itemIdSchema,
} from '../schemas/item.schema.js';
import * as itemController from '../controllers/item.controller.js';

const router = Router();

// GET /api/items - List items
router.get('/', itemController.listItems);

// GET /api/items/:id - Get single item
router.get('/:id', validateParams(itemIdSchema), itemController.getItem);

// POST /api/items - Create item (requires VOLUNTEER+)
router.post(
  '/',
  requireAuth,
  requireMinRole('VOLUNTEER'),
  validateBody(createItemSchema),
  itemController.createItem
);

// PUT /api/items/:id - Update item (requires VOLUNTEER+)
router.put(
  '/:id',
  requireAuth,
  requireMinRole('VOLUNTEER'),
  validateParams(itemIdSchema),
  validateBody(updateItemSchema),
  itemController.updateItem
);

// DELETE /api/items/:id - Delete item (requires COORDINATOR+)
router.delete(
  '/:id',
  requireAuth,
  requireMinRole('COORDINATOR'),
  validateParams(itemIdSchema),
  itemController.deleteItem
);

// POST /api/items/:id/receive - Mark item as received
router.post(
  '/:id/receive',
  requireAuth,
  requireMinRole('VOLUNTEER'),
  validateParams(itemIdSchema),
  itemController.markReceived
);

export default router;
