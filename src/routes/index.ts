import { Router } from 'express';
import authRoutes from './auth.routes.js';
import orgRoutes from './org.routes.js';
import itemRoutes from './item.routes.js';
import userRoutes from './user.routes.js';
import reportRoutes from './report.routes.js';
import noteRoutes from './note.routes.js';
import { apiLimiter } from '../middleware/rate-limiter.js';

const router = Router();

// Apply rate limiter to all API routes
router.use(apiLimiter);

// Health check
router.get('/health', (_req, res) => {
  res.json({ status: 'ok', timestamp: new Date().toISOString() });
});

// API routes
router.use('/auth', authRoutes);
router.use('/orgs', orgRoutes);
router.use('/items', itemRoutes);
router.use('/users', userRoutes);
router.use('/reports', reportRoutes);
router.use('/notes', noteRoutes);

export default router;
