import { Request, Response, NextFunction, ErrorRequestHandler } from 'express';
import { ZodError } from 'zod';
import { logger } from '../utils/logger.js';
import {
  sendError,
  sendServerError,
  sendValidationError,
} from '../utils/response.js';
import { env } from '../config/env.js';

export class AppError extends Error {
  constructor(
    public statusCode: number,
    public code: string,
    message: string,
    public details?: unknown
  ) {
    super(message);
    this.name = 'AppError';
    Error.captureStackTrace(this, this.constructor);
  }
}

export class NotFoundError extends AppError {
  constructor(resource = 'Resource') {
    super(404, 'NOT_FOUND', `${resource} not found`);
  }
}

export class UnauthorizedError extends AppError {
  constructor(message = 'Unauthorized') {
    super(401, 'UNAUTHORIZED', message);
  }
}

export class ForbiddenError extends AppError {
  constructor(message = 'Forbidden') {
    super(403, 'FORBIDDEN', message);
  }
}

export class ValidationError extends AppError {
  constructor(details: unknown) {
    super(400, 'VALIDATION_ERROR', 'Validation failed', details);
  }
}

export const errorHandler: ErrorRequestHandler = (
  err: Error,
  _req: Request,
  res: Response,
  _next: NextFunction
): void => {
  // Log error
  logger.error({ err }, 'Error occurred');

  // Zod validation error
  if (err instanceof ZodError) {
    sendValidationError(res, err.errors);
    return;
  }

  // Custom app error
  if (err instanceof AppError) {
    sendError(res, err.code, err.message, err.statusCode, err.details);
    return;
  }

  // Unknown error
  const message =
    env.NODE_ENV === 'production' ? 'Internal server error' : err.message;

  sendServerError(res, message);
};

// 404 handler for undefined routes
export function notFoundHandler(req: Request, res: Response): void {
  sendError(res, 'NOT_FOUND', `Route ${req.method} ${req.path} not found`, 404);
}
