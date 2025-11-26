import { Request, Response, NextFunction } from 'express';
import { ZodSchema, ZodError } from 'zod';
import { sendValidationError } from '../utils/response.js';

type ValidationTarget = 'body' | 'query' | 'params';

interface ValidateOptions {
  target?: ValidationTarget;
}

export function validate<T>(schema: ZodSchema<T>, options: ValidateOptions = {}) {
  const { target = 'body' } = options;

  return (req: Request, res: Response, next: NextFunction): void => {
    try {
      const data = req[target];
      const validated = schema.parse(data);

      // Replace request data with validated data
      if (target === 'body') {
        req.body = validated;
      } else if (target === 'query') {
        // Type assertion needed for query
        (req as unknown as { query: T }).query = validated;
      } else if (target === 'params') {
        (req as unknown as { params: T }).params = validated;
      }

      next();
    } catch (error) {
      if (error instanceof ZodError) {
        const formattedErrors = error.errors.map((err) => ({
          field: err.path.join('.'),
          message: err.message,
        }));
        sendValidationError(res, formattedErrors);
        return;
      }
      next(error);
    }
  };
}

export function validateBody<T>(schema: ZodSchema<T>) {
  return validate(schema, { target: 'body' });
}

export function validateQuery<T>(schema: ZodSchema<T>) {
  return validate(schema, { target: 'query' });
}

export function validateParams<T>(schema: ZodSchema<T>) {
  return validate(schema, { target: 'params' });
}
