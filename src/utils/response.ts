import { Response } from 'express';

export interface ApiResponse<T = unknown> {
  success: boolean;
  data?: T;
  error?: {
    code: string;
    message: string;
    details?: unknown;
  };
  meta?: {
    page?: number;
    limit?: number;
    total?: number;
    totalPages?: number;
  };
}

export function sendSuccess<T>(
  res: Response,
  data: T,
  statusCode = 200,
  meta?: ApiResponse['meta']
): Response {
  const response: ApiResponse<T> = {
    success: true,
    data,
    ...(meta && { meta }),
  };
  return res.status(statusCode).json(response);
}

export function sendError(
  res: Response,
  code: string,
  message: string,
  statusCode = 400,
  details?: unknown
): Response {
  const error: ApiResponse['error'] = {
    code,
    message,
  };
  if (details !== undefined) {
    error.details = details;
  }
  const response: ApiResponse = {
    success: false,
    error,
  };
  return res.status(statusCode).json(response);
}

export function sendNotFound(res: Response, resource = 'Resource'): Response {
  return sendError(res, 'NOT_FOUND', `${resource} not found`, 404);
}

export function sendUnauthorized(
  res: Response,
  message = 'Unauthorized'
): Response {
  return sendError(res, 'UNAUTHORIZED', message, 401);
}

export function sendForbidden(res: Response, message = 'Forbidden'): Response {
  return sendError(res, 'FORBIDDEN', message, 403);
}

export function sendValidationError(res: Response, details: unknown): Response {
  return sendError(res, 'VALIDATION_ERROR', 'Validation failed', 400, details);
}

export function sendServerError(
  res: Response,
  message = 'Internal server error'
): Response {
  return sendError(res, 'SERVER_ERROR', message, 500);
}
