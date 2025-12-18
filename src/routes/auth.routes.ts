import { Router } from 'express';
import { authLimiter } from '../middleware/rate-limiter.js';
import { validateBody } from '../middleware/validation.js';
import { requireAuth } from '../middleware/auth.js';
import {
  loginSchema,
  registerSchema,
  forgotPasswordSchema,
  resetPasswordSchema,
  changePasswordSchema,
} from '../schemas/auth.schema.js';
import * as authController from '../controllers/auth.controller.js';

const router = Router();

// Apply strict rate limiting to auth routes
router.use(authLimiter);

// POST /api/auth/register
router.post('/register', validateBody(registerSchema), authController.register);

// POST /api/auth/login
router.post('/login', validateBody(loginSchema), authController.login);

// POST /api/auth/logout
router.post('/logout', authController.logout);

// POST /api/auth/forgot-password
router.post(
  '/forgot-password',
  validateBody(forgotPasswordSchema),
  authController.forgotPassword
);

// POST /api/auth/reset-password
router.post(
  '/reset-password',
  validateBody(resetPasswordSchema),
  authController.resetPassword
);

// POST /api/auth/change-password (requires authentication)
router.post(
  '/change-password',
  requireAuth,
  validateBody(changePasswordSchema),
  authController.changePassword
);

// GET /api/auth/me - Get current user
router.get('/me', authController.getCurrentUser);

export default router;
