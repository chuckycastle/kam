import { Request, Response, NextFunction } from 'express';
import { prisma } from '../config/database.js';
import { sendSuccess, sendNotFound, sendForbidden } from '../utils/response.js';
import { logger } from '../utils/logger.js';
import { Role } from '@prisma/client';

export async function createNote(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { content, organizationId } = req.body;
    const authorId = req.user!.id;

    // Verify organization exists
    const organization = await prisma.organization.findUnique({
      where: { id: organizationId },
    });

    if (!organization) {
      sendNotFound(res, 'Organization');
      return;
    }

    const note = await prisma.note.create({
      data: {
        content,
        organizationId,
        authorId,
      },
      include: {
        author: {
          select: { id: true, username: true, firstName: true, lastName: true },
        },
        organization: {
          select: { id: true, name: true },
        },
      },
    });

    logger.info({ noteId: note.id, orgId: organizationId }, 'Note created');

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

    const note = await prisma.note.findUnique({
      where: { id },
      include: {
        author: {
          select: { id: true, username: true, firstName: true, lastName: true },
        },
        organization: {
          select: { id: true, name: true },
        },
      },
    });

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

    const existing = await prisma.note.findUnique({
      where: { id },
    });

    if (!existing) {
      sendNotFound(res, 'Note');
      return;
    }

    // Only author can update their note
    if (existing.authorId !== userId) {
      sendForbidden(res, 'You can only edit your own notes');
      return;
    }

    const note = await prisma.note.update({
      where: { id },
      data: { content },
      include: {
        author: {
          select: { id: true, username: true, firstName: true, lastName: true },
        },
        organization: {
          select: { id: true, name: true },
        },
      },
    });

    logger.info({ noteId: id }, 'Note updated');

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

    const existing = await prisma.note.findUnique({
      where: { id },
    });

    if (!existing) {
      sendNotFound(res, 'Note');
      return;
    }

    // Author or admin can delete
    const isAdmin = userRole === Role.SUPER_ADMIN || userRole === Role.ADMIN;
    if (existing.authorId !== userId && !isAdmin) {
      sendForbidden(res, 'You can only delete your own notes');
      return;
    }

    await prisma.note.delete({ where: { id } });

    logger.info({ noteId: id }, 'Note deleted');

    sendSuccess(res, { message: 'Note deleted' });
  } catch (error) {
    next(error);
  }
}
