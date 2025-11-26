import { Request, Response, NextFunction } from 'express';
import { prisma } from '../config/database.js';
import { sendSuccess } from '../utils/response.js';

export async function getDashboard(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const userId = req.user?.id || '';
    void userId; // Acknowledge req usage

    const [
      totalOrgs,
      myOrgs,
      totalItems,
      receivedItems,
      pendingItems,
    ] = await Promise.all([
      prisma.organization.count(),
      prisma.organization.count({ where: { assignedToId: userId } }),
      prisma.item.count(),
      prisma.item.count({ where: { isReceived: true } }),
      prisma.item.count({ where: { isReceived: false } }),
    ]);

    // Calculate total value
    const valueResult = await prisma.item.aggregate({
      _sum: { value: true },
      where: { isReceived: true },
    });

    sendSuccess(res, {
      organizations: {
        total: totalOrgs,
        assigned: myOrgs,
      },
      items: {
        total: totalItems,
        received: receivedItems,
        pending: pendingItems,
        totalValue: valueResult._sum.value || 0,
      },
    });
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
    // Get users with most items
    const leaderboard = await prisma.user.findMany({
      where: { isActive: true },
      select: {
        id: true,
        username: true,
        firstName: true,
        lastName: true,
        _count: {
          select: {
            items: true,
            organizations: true,
          },
        },
      },
      orderBy: {
        items: { _count: 'desc' },
      },
      take: 10,
    });

    // Calculate points (items * 1 + orgs * 5)
    const rankedLeaderboard = leaderboard.map((user, index) => ({
      rank: index + 1,
      id: user.id,
      username: user.username,
      name: `${user.firstName} ${user.lastName}`,
      itemsCount: user._count.items,
      organizationsCount: user._count.organizations,
      points: user._count.items + user._count.organizations * 5,
    }));

    sendSuccess(res, rankedLeaderboard);
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
    const [byStatus, recentItems] = await Promise.all([
      // Items by status
      prisma.item.groupBy({
        by: ['isReceived'],
        _count: true,
        _sum: { value: true },
      }),
      // Recent items
      prisma.item.findMany({
        orderBy: { createdAt: 'desc' },
        take: 10,
        include: {
          organization: { select: { name: true } },
          createdBy: { select: { username: true, firstName: true, lastName: true } },
        },
      }),
    ]);

    const summary = {
      received: byStatus.find((s) => s.isReceived)?._count || 0,
      pending: byStatus.find((s) => !s.isReceived)?._count || 0,
      totalValue: byStatus.reduce((acc, s) => acc + Number(s._sum.value || 0), 0),
      recentItems,
    };

    sendSuccess(res, summary);
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
    const categories = await prisma.category.findMany({
      include: {
        organizations: {
          include: {
            _count: { select: { items: true } },
          },
        },
      },
    });

    const breakdown = categories.map((cat) => ({
      id: cat.id,
      title: cat.title,
      organizationCount: cat.organizations.length,
      itemCount: cat.organizations.reduce((acc, org) => acc + org._count.items, 0),
    }));

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
    const users = await prisma.user.findMany({
      where: { isActive: true },
      select: {
        id: true,
        username: true,
        firstName: true,
        lastName: true,
        role: true,
        createdAt: true,
        _count: {
          select: {
            organizations: true,
            items: true,
            notes: true,
          },
        },
      },
      orderBy: { createdAt: 'desc' },
    });

    const activity = users.map((user) => ({
      id: user.id,
      username: user.username,
      name: `${user.firstName} ${user.lastName}`,
      role: user.role,
      joinedAt: user.createdAt,
      stats: {
        organizations: user._count.organizations,
        items: user._count.items,
        notes: user._count.notes,
      },
    }));

    sendSuccess(res, activity);
  } catch (error) {
    next(error);
  }
}
