import { Request, Response, NextFunction } from 'express';
import { supabase } from '../config/database.js';
import { sendUnauthorized, sendForbidden } from '../utils/response.js';
import { Role, ROLE_LEVELS } from '../config/constants.js';

// Extend Express Request to include user
declare global {
  namespace Express {
    interface Request {
      user?: {
        id: string;
        email: string;
        role: Role;
      };
    }
  }
}

// Require authentication
export async function requireAuth(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    // Get token from header or cookie
    const authHeader = req.headers.authorization;
    const token = authHeader?.startsWith('Bearer ')
      ? authHeader.substring(7)
      : req.cookies?.['kam.token'];

    if (!token) {
      sendUnauthorized(res, 'No authentication token provided');
      return;
    }

    // Verify token with Supabase
    const { data: { user }, error } = await supabase.auth.getUser(token);

    if (error || !user) {
      sendUnauthorized(res, 'Invalid or expired token');
      return;
    }

    // Get user role from metadata or database
    const role = (user.user_metadata?.role as Role) || 'VOLUNTEER';

    req.user = {
      id: user.id,
      email: user.email || '',
      role,
    };

    next();
  } catch (error) {
    next(error);
  }
}

// Optional authentication (doesn't fail if no token)
export async function optionalAuth(
  req: Request,
  _res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const authHeader = req.headers.authorization;
    const token = authHeader?.startsWith('Bearer ')
      ? authHeader.substring(7)
      : req.cookies?.['kam.token'];

    if (token) {
      const { data: { user } } = await supabase.auth.getUser(token);

      if (user) {
        const role = (user.user_metadata?.role as Role) || 'VOLUNTEER';
        req.user = {
          id: user.id,
          email: user.email || '',
          role,
        };
      }
    }

    next();
  } catch {
    // Ignore errors for optional auth
    next();
  }
}

// Require minimum role level
export function requireRole(...allowedRoles: Role[]) {
  return (req: Request, res: Response, next: NextFunction): void => {
    if (!req.user) {
      sendUnauthorized(res, 'Authentication required');
      return;
    }

    if (!allowedRoles.includes(req.user.role)) {
      sendForbidden(res, 'Insufficient permissions');
      return;
    }

    next();
  };
}

// Require minimum role level (hierarchical)
export function requireMinRole(minRole: Role) {
  return (req: Request, res: Response, next: NextFunction): void => {
    if (!req.user) {
      sendUnauthorized(res, 'Authentication required');
      return;
    }

    const userLevel = ROLE_LEVELS[req.user.role];
    const requiredLevel = ROLE_LEVELS[minRole];

    if (userLevel > requiredLevel) {
      sendForbidden(res, 'Insufficient permissions');
      return;
    }

    next();
  };
}
