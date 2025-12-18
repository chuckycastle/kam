import { prisma } from '../config/database.js';

// Business rule constants
export const POINTS = {
  PER_ITEM: 1,
  PER_ORGANIZATION: 5,
};

export interface DashboardStats {
  organizations: {
    total: number;
    assigned: number;
  };
  items: {
    total: number;
    received: number;
    pending: number;
    totalValue: number;
  };
}

export interface LeaderboardEntry {
  rank: number;
  id: string;
  username: string;
  name: string;
  itemsCount: number;
  organizationsCount: number;
  points: number;
}

export interface CategoryBreakdown {
  id: string;
  title: string;
  organizationCount: number;
  itemCount: number;
}

export interface UserActivity {
  id: string;
  name: string;
  stats: {
    organizations: number;
    items: number;
    notes: number;
  };
}

/**
 * Get dashboard statistics
 */
export async function getDashboardStats(
  userId?: string
): Promise<DashboardStats> {
  const [
    totalOrgs,
    myOrgs,
    totalItems,
    receivedItems,
    pendingItems,
    valueResult,
  ] = await Promise.all([
    prisma.organization.count(),
    userId
      ? prisma.organization.count({ where: { assignedToId: userId } })
      : Promise.resolve(0),
    prisma.item.count(),
    prisma.item.count({ where: { isReceived: true } }),
    prisma.item.count({ where: { isReceived: false } }),
    prisma.item.aggregate({
      _sum: { value: true },
      where: { isReceived: true },
    }),
  ]);

  return {
    organizations: {
      total: totalOrgs,
      assigned: myOrgs,
    },
    items: {
      total: totalItems,
      received: receivedItems,
      pending: pendingItems,
      totalValue: Number(valueResult._sum.value) || 0,
    },
  };
}

/**
 * Get leaderboard with points calculation
 */
export async function getLeaderboard(limit = 10): Promise<LeaderboardEntry[]> {
  const users = await prisma.user.findMany({
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
    take: limit,
  });

  return users.map((user, index) => ({
    rank: index + 1,
    id: user.id,
    username: user.username,
    name: `${user.firstName} ${user.lastName}`,
    itemsCount: user._count.items,
    organizationsCount: user._count.organizations,
    points:
      user._count.items * POINTS.PER_ITEM +
      user._count.organizations * POINTS.PER_ORGANIZATION,
  }));
}

/**
 * Get items summary (recent items)
 */
export async function getItemsSummary(limit = 10) {
  return prisma.item.findMany({
    orderBy: { createdAt: 'desc' },
    take: limit,
    select: {
      id: true,
      name: true,
      value: true,
      isReceived: true,
      createdAt: true,
      organization: {
        select: { id: true, name: true },
      },
      createdBy: {
        select: { firstName: true, lastName: true },
      },
    },
  });
}

/**
 * Get category breakdown statistics
 */
export async function getCategoryBreakdown(): Promise<CategoryBreakdown[]> {
  const categories = await prisma.category.findMany({
    include: {
      organizations: {
        include: {
          _count: { select: { items: true } },
        },
      },
    },
  });

  return categories.map((cat) => ({
    id: cat.id,
    title: cat.title,
    organizationCount: cat.organizations.length,
    itemCount: cat.organizations.reduce(
      (acc, org) => acc + org._count.items,
      0
    ),
  }));
}

/**
 * Get user activity statistics
 */
export async function getUserActivity(limit = 10): Promise<UserActivity[]> {
  const users = await prisma.user.findMany({
    where: { isActive: true },
    select: {
      id: true,
      firstName: true,
      lastName: true,
      _count: {
        select: {
          organizations: true,
          items: true,
          notes: true,
        },
      },
    },
    orderBy: {
      items: { _count: 'desc' },
    },
    take: limit,
  });

  return users.map((user) => ({
    id: user.id,
    name: `${user.firstName} ${user.lastName}`,
    stats: {
      organizations: user._count.organizations,
      items: user._count.items,
      notes: user._count.notes,
    },
  }));
}

/**
 * Get categories list with organization counts
 */
export async function getCategories() {
  const categories = await prisma.category.findMany({
    orderBy: { title: 'asc' },
    select: {
      id: true,
      title: true,
      description: true,
      _count: { select: { organizations: true } },
    },
  });

  return categories.map((cat) => ({
    id: cat.id,
    name: cat.title,
    description: cat.description,
    organizationCount: cat._count.organizations,
  }));
}
