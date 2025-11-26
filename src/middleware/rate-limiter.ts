import rateLimit from 'express-rate-limit';
import { RATE_LIMITS } from '../config/constants.js';
import { sendError } from '../utils/response.js';

// General API rate limiter
export const apiLimiter = rateLimit({
  windowMs: RATE_LIMITS.API.windowMs,
  max: RATE_LIMITS.API.max,
  standardHeaders: true,
  legacyHeaders: false,
  handler: (_req, res) => {
    sendError(res, 'RATE_LIMITED', 'Too many requests, please try again later', 429);
  },
});

// Strict rate limiter for auth endpoints
export const authLimiter = rateLimit({
  windowMs: RATE_LIMITS.AUTH.windowMs,
  max: RATE_LIMITS.AUTH.max,
  standardHeaders: true,
  legacyHeaders: false,
  handler: (_req, res) => {
    sendError(
      res,
      'RATE_LIMITED',
      'Too many authentication attempts, please try again later',
      429
    );
  },
});

// Create custom rate limiter
export function createRateLimiter(windowMs: number, max: number) {
  return rateLimit({
    windowMs,
    max,
    standardHeaders: true,
    legacyHeaders: false,
    handler: (_req, res) => {
      sendError(res, 'RATE_LIMITED', 'Too many requests, please try again later', 429);
    },
  });
}
