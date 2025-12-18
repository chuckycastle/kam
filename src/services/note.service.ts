import { Role } from '@prisma/client';
import { prisma } from '../config/database.js';
import { logger } from '../utils/logger.js';
import { isAdmin } from './authorization.service.js';

const noteSelect = {
  id: true,
  content: true,
  createdAt: true,
  author: {
    select: { id: true, username: true, firstName: true, lastName: true },
  },
  organization: {
    select: { id: true, name: true },
  },
};

/**
 * Create a new note
 */
export async function createNote(data: {
  content: string;
  organizationId: string;
  authorId: string;
}) {
  const note = await prisma.note.create({
    data: {
      content: data.content,
      organizationId: data.organizationId,
      authorId: data.authorId,
    },
    select: noteSelect,
  });

  logger.info({ noteId: note.id, orgId: data.organizationId }, 'Note created');
  return note;
}

/**
 * Get note by ID
 */
export async function getNoteById(id: string) {
  return prisma.note.findUnique({
    where: { id },
    select: noteSelect,
  });
}

/**
 * Update a note
 */
export async function updateNote(id: string, content: string) {
  const note = await prisma.note.update({
    where: { id },
    data: { content },
    select: noteSelect,
  });

  logger.info({ noteId: id }, 'Note updated');
  return note;
}

/**
 * Delete a note
 */
export async function deleteNote(id: string): Promise<void> {
  await prisma.note.delete({ where: { id } });
  logger.info({ noteId: id }, 'Note deleted');
}

/**
 * Get note ownership for permission checks
 */
export async function getNoteOwnership(
  id: string
): Promise<{ exists: boolean; authorId: string | null }> {
  const note = await prisma.note.findUnique({
    where: { id },
    select: { authorId: true },
  });

  return {
    exists: !!note,
    authorId: note?.authorId || null,
  };
}

/**
 * Check if user can edit a note (only author)
 */
export function canEditNote(userId: string, authorId: string): boolean {
  return userId === authorId;
}

/**
 * Check if user can delete a note (author or admin)
 */
export function canDeleteNote(
  userId: string,
  authorId: string,
  userRole: Role
): boolean {
  return userId === authorId || isAdmin(userRole);
}
