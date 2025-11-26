// Application constants

export const APP_NAME = 'Krieger Auction Manager';
export const APP_SHORT_NAME = 'KAM';
export const APP_VERSION = '1.0.0';

// User roles
export const ROLES = {
  SUPER_ADMIN: 'SUPER_ADMIN',
  ADMIN: 'ADMIN',
  COORDINATOR: 'COORDINATOR',
  VOLUNTEER: 'VOLUNTEER',
  PUBLIC: 'PUBLIC',
} as const;

export type Role = (typeof ROLES)[keyof typeof ROLES];

// Role hierarchy (lower number = higher privilege)
export const ROLE_LEVELS: Record<Role, number> = {
  SUPER_ADMIN: 1,
  ADMIN: 2,
  COORDINATOR: 3,
  VOLUNTEER: 4,
  PUBLIC: 5,
};

// Pagination defaults
export const PAGINATION = {
  DEFAULT_PAGE: 1,
  DEFAULT_LIMIT: 20,
  MAX_LIMIT: 100,
} as const;

// Session config
export const SESSION = {
  COOKIE_NAME: 'kam.sid',
  MAX_AGE: 24 * 60 * 60 * 1000, // 24 hours
} as const;

// Rate limiting presets
export const RATE_LIMITS = {
  AUTH: {
    windowMs: 15 * 60 * 1000, // 15 minutes
    max: 5, // 5 attempts per window
  },
  API: {
    windowMs: 60 * 1000, // 1 minute
    max: 60, // 60 requests per minute
  },
} as const;
