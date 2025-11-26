import { Request, Response, NextFunction } from 'express';
import { prisma } from '../config/database.js';
import { sendSuccess, sendNotFound, sendForbidden } from '../utils/response.js';
import { logger } from '../utils/logger.js';
import { PAGINATION, ROLE_LEVELS } from '../config/constants.js';

export async function listUsers(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const page = Number(req.query.page) || PAGINATION.DEFAULT_PAGE;
    const limit = Math.min(Number(req.query.limit) || PAGINATION.DEFAULT_LIMIT, PAGINATION.MAX_LIMIT);
    const search = req.query.search as string | undefined;
    const role = req.query.role as string | undefined;
    const isActive = req.query.isActive === 'true' ? true : req.query.isActive === 'false' ? false : undefined;
    const sortBy = (req.query.sortBy as string) || 'username';
    const sortOrder = (req.query.sortOrder as 'asc' | 'desc') || 'asc';

    const skip = (page - 1) * limit;

    const where = {
      ...(search && {
        OR: [
          { username: { contains: search, mode: 'insensitive' as const } },
          { email: { contains: search, mode: 'insensitive' as const } },
          { firstName: { contains: search, mode: 'insensitive' as const } },
          { lastName: { contains: search, mode: 'insensitive' as const } },
        ],
      }),
      ...(role && { role: role as 'SUPER_ADMIN' | 'ADMIN' | 'COORDINATOR' | 'VOLUNTEER' | 'PUBLIC' }),
      ...(isActive !== undefined && { isActive }),
    };

    const [users, total] = await Promise.all([
      prisma.user.findMany({
        where,
        skip,
        take: limit,
        orderBy: { [sortBy]: sortOrder },
        select: {
          id: true,
          email: true,
          username: true,
          firstName: true,
          lastName: true,
          role: true,
          isActive: true,
          createdAt: true,
          _count: { select: { organizations: true, items: true } },
        },
      }),
      prisma.user.count({ where }),
    ]);

    sendSuccess(res, users, 200, {
      page,
      limit,
      total,
      totalPages: Math.ceil(total / limit),
    });
  } catch (error) {
    next(error);
  }
}

export async function getUser(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { id } = req.params;
    const currentUser = req.user!;

    // Users can view themselves, admins can view anyone
    const canView =
      id === currentUser.id ||
      ROLE_LEVELS[currentUser.role] <= ROLE_LEVELS.ADMIN;

    if (!canView) {
      sendForbidden(res, 'You can only view your own profile');
      return;
    }

    const user = await prisma.user.findUnique({
      where: { id },
      select: {
        id: true,
        email: true,
        username: true,
        firstName: true,
        lastName: true,
        role: true,
        isActive: true,
        createdAt: true,
        updatedAt: true,
        supervisor: {
          select: { id: true, username: true, firstName: true, lastName: true },
        },
        _count: { select: { organizations: true, items: true, notes: true } },
      },
    });

    if (!user) {
      sendNotFound(res, 'User');
      return;
    }

    sendSuccess(res, user);
  } catch (error) {
    next(error);
  }
}

export async function updateUser(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { id } = req.params;
    const data = req.body;
    const currentUser = req.user!;

    // Check if user exists
    const existing = await prisma.user.findUnique({ where: { id } });
    if (!existing) {
      sendNotFound(res, 'User');
      return;
    }

    // Users can update themselves (limited fields), admins can update anyone
    const isAdmin = ROLE_LEVELS[currentUser.role] <= ROLE_LEVELS.ADMIN;
    const isSelf = id === currentUser.id;

    if (!isAdmin && !isSelf) {
      sendForbidden(res, 'You can only update your own profile');
      return;
    }

    // Non-admins can't change role, isActive, or supervisorId
    if (!isAdmin) {
      delete data.role;
      delete data.isActive;
      delete data.supervisorId;
    }

    // Prevent non-super-admins from creating super admins
    if (data.role === 'SUPER_ADMIN' && currentUser.role !== 'SUPER_ADMIN') {
      sendForbidden(res, 'Only super admins can assign super admin role');
      return;
    }

    const user = await prisma.user.update({
      where: { id },
      data,
      select: {
        id: true,
        email: true,
        username: true,
        firstName: true,
        lastName: true,
        role: true,
        isActive: true,
        updatedAt: true,
      },
    });

    logger.info({ userId: id, updatedBy: currentUser.id }, 'User updated');

    sendSuccess(res, user);
  } catch (error) {
    next(error);
  }
}

export async function deleteUser(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { id } = req.params;
    const currentUser = req.user!;

    // Can't delete yourself
    if (id === currentUser.id) {
      sendForbidden(res, 'You cannot delete your own account');
      return;
    }

    const existing = await prisma.user.findUnique({ where: { id } });
    if (!existing) {
      sendNotFound(res, 'User');
      return;
    }

    // Soft delete - just deactivate
    await prisma.user.update({
      where: { id },
      data: { isActive: false },
    });

    logger.info({ userId: id, deletedBy: currentUser.id }, 'User deactivated');

    sendSuccess(res, { message: 'User deactivated' });
  } catch (error) {
    next(error);
  }
}

export async function getUserOrganizations(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { id } = req.params;
    const currentUser = req.user!;

    // Users can view their own orgs, admins can view anyone's
    const canView =
      id === currentUser.id ||
      ROLE_LEVELS[currentUser.role] <= ROLE_LEVELS.ADMIN;

    if (!canView) {
      sendForbidden(res, 'You can only view your own organizations');
      return;
    }

    const organizations = await prisma.organization.findMany({
      where: { assignedToId: id },
      orderBy: { name: 'asc' },
      include: {
        category: true,
        _count: { select: { items: true, notes: true } },
      },
    });

    sendSuccess(res, organizations);
  } catch (error) {
    next(error);
  }
}
