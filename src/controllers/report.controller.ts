import { Request, Response, NextFunction } from 'express';
import { sendSuccess } from '../utils/response.js';
import { reportService } from '../services/index.js';

export async function getDashboard(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const userId = req.user?.id;

    const stats = await reportService.getDashboardStats(userId);

    sendSuccess(res, stats);
  } catch (error) {
    next(error);
  }
}

export async function getLeaderboard(
  _req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const leaderboard = await reportService.getLeaderboard();

    sendSuccess(res, leaderboard);
  } catch (error) {
    next(error);
  }
}

export async function getItemsSummary(
  _req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const items = await reportService.getItemsSummary();

    sendSuccess(res, items);
  } catch (error) {
    next(error);
  }
}

export async function getCategoryBreakdown(
  _req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const breakdown = await reportService.getCategoryBreakdown();

    sendSuccess(res, breakdown);
  } catch (error) {
    next(error);
  }
}

export async function getUserActivity(
  _req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const activity = await reportService.getUserActivity();

    sendSuccess(res, activity);
  } catch (error) {
    next(error);
  }
}

export async function getCategories(
  _req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const categories = await reportService.getCategories();

    sendSuccess(res, categories);
  } catch (error) {
    next(error);
  }
}
