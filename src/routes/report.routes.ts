import { Router } from 'express';
import { requireAuth, requireMinRole } from '../middleware/auth.js';
import * as reportController from '../controllers/report.controller.js';

const router = Router();

// GET /api/reports/dashboard - Dashboard stats
router.get('/dashboard', requireAuth, reportController.getDashboard);

// GET /api/reports/leaderboard - Participation leaderboard
router.get('/leaderboard', reportController.getLeaderboard);

// GET /api/reports/items-summary - Items summary
router.get(
  '/items-summary',
  requireAuth,
  requireMinRole('COORDINATOR'),
  reportController.getItemsSummary
);

// GET /api/reports/category-breakdown - Items by category
router.get(
  '/category-breakdown',
  requireAuth,
  requireMinRole('COORDINATOR'),
  reportController.getCategoryBreakdown
);

// GET /api/reports/user-activity - User activity report (requires ADMIN+)
router.get(
  '/user-activity',
  requireAuth,
  requireMinRole('ADMIN'),
  reportController.getUserActivity
);

// GET /api/reports/categories - List all categories
router.get('/categories', reportController.getCategories);

export default router;
