import { Router } from 'express';
import { requireAuth } from '../middleware/auth.js';
import { validateBody, validateParams } from '../middleware/validation.js';
import {
  createNoteSchema,
  updateNoteSchema,
  noteIdSchema,
} from '../schemas/note.schema.js';
import * as noteController from '../controllers/note.controller.js';

const router = Router();

// POST /api/notes - Create note (authenticated)
router.post(
  '/',
  requireAuth,
  validateBody(createNoteSchema),
  noteController.createNote
);

// GET /api/notes/:id - Get single note
router.get(
  '/:id',
  requireAuth,
  validateParams(noteIdSchema),
  noteController.getNote
);

// PUT /api/notes/:id - Update note (author only)
router.put(
  '/:id',
  requireAuth,
  validateParams(noteIdSchema),
  validateBody(updateNoteSchema),
  noteController.updateNote
);

// DELETE /api/notes/:id - Delete note (author or admin)
router.delete(
  '/:id',
  requireAuth,
  validateParams(noteIdSchema),
  noteController.deleteNote
);

export default router;
