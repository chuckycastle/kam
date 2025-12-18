import { Request, Response, NextFunction } from 'express';
import { sendSuccess, sendNotFound, sendForbidden } from '../utils/response.js';
import { noteService, organizationService } from '../services/index.js';

export async function createNote(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { content, organizationId } = req.body;
    const authorId = req.user!.id;

    // Verify organization exists
    const orgExists =
      await organizationService.organizationExists(organizationId);
    if (!orgExists) {
      sendNotFound(res, 'Organization');
      return;
    }

    const note = await noteService.createNote({
      content,
      organizationId,
      authorId,
    });

    sendSuccess(res, note, 201);
  } catch (error) {
    next(error);
  }
}

export async function getNote(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { id } = req.params;

    const note = await noteService.getNoteById(id);

    if (!note) {
      sendNotFound(res, 'Note');
      return;
    }

    sendSuccess(res, note);
  } catch (error) {
    next(error);
  }
}

export async function updateNote(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { id } = req.params;
    const { content } = req.body;
    const userId = req.user!.id;

    const ownership = await noteService.getNoteOwnership(id);
    if (!ownership.exists) {
      sendNotFound(res, 'Note');
      return;
    }

    // Only author can update their note
    if (!noteService.canEditNote(userId, ownership.authorId!)) {
      sendForbidden(res, 'You can only edit your own notes');
      return;
    }

    const note = await noteService.updateNote(id, content);

    sendSuccess(res, note);
  } catch (error) {
    next(error);
  }
}

export async function deleteNote(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { id } = req.params;
    const userId = req.user!.id;
    const userRole = req.user!.role;

    const ownership = await noteService.getNoteOwnership(id);
    if (!ownership.exists) {
      sendNotFound(res, 'Note');
      return;
    }

    // Author or admin can delete
    if (!noteService.canDeleteNote(userId, ownership.authorId!, userRole)) {
      sendForbidden(res, 'You can only delete your own notes');
      return;
    }

    await noteService.deleteNote(id);

    sendSuccess(res, { message: 'Note deleted' });
  } catch (error) {
    next(error);
  }
}
