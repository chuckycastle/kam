import { z } from 'zod';

export const noteIdSchema = z.object({
  id: z.string().uuid('Invalid note ID'),
});

export const createNoteSchema = z.object({
  content: z.string().min(1, 'Content is required').max(5000),
  organizationId: z.string().uuid('Invalid organization ID'),
});

export const updateNoteSchema = z.object({
  content: z.string().min(1, 'Content is required').max(5000),
});

export type NoteId = z.infer<typeof noteIdSchema>;
export type CreateNoteInput = z.infer<typeof createNoteSchema>;
export type UpdateNoteInput = z.infer<typeof updateNoteSchema>;
