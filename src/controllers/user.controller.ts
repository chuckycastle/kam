import { Request, Response, NextFunction } from 'express';
import { sendSuccess, sendNotFound, sendForbidden } from '../utils/response.js';
import {
  userService,
  canViewProfile,
  filterUserUpdateFields,
  isAdmin,
  isSuperAdmin,
} from '../services/index.js';

export async function listUsers(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const filters = {
      page: Number(req.query.page) || undefined,
      limit: Number(req.query.limit) || undefined,
      search: req.query.search as string | undefined,
      role: req.query.role as string | undefined,
      isActive:
        req.query.isActive === 'true'
          ? true
          : req.query.isActive === 'false'
            ? false
            : undefined,
      sortBy: req.query.sortBy as string | undefined,
      sortOrder: req.query.sortOrder as 'asc' | 'desc' | undefined,
    };

    const result = await userService.listUsers(filters);

    sendSuccess(res, result.users, 200, result.meta);
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

    if (!canViewProfile(currentUser.id, id, currentUser.role)) {
      sendForbidden(res, 'You can only view your own profile');
      return;
    }

    const user = await userService.getUserById(id);

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

    const exists = await userService.userExists(id);
    if (!exists) {
      sendNotFound(res, 'User');
      return;
    }

    const isSelf = id === currentUser.id;
    const userIsAdmin = isAdmin(currentUser.role);

    if (!userIsAdmin && !isSelf) {
      sendForbidden(res, 'You can only update your own profile');
      return;
    }

    // Prevent non-super-admins from creating super admins
    if (data.role === 'SUPER_ADMIN' && !isSuperAdmin(currentUser.role)) {
      sendForbidden(res, 'Only super admins can assign super admin role');
      return;
    }

    // Filter fields based on permissions
    const filteredData = filterUserUpdateFields(data, isSelf, currentUser.role);

    const user = await userService.updateUser(id, filteredData);

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

    if (id === currentUser.id) {
      sendForbidden(res, 'You cannot delete your own account');
      return;
    }

    const exists = await userService.userExists(id);
    if (!exists) {
      sendNotFound(res, 'User');
      return;
    }

    await userService.deactivateUser(id, currentUser.id);

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

    if (!canViewProfile(currentUser.id, id, currentUser.role)) {
      sendForbidden(res, 'You can only view your own organizations');
      return;
    }

    const organizations = await userService.getUserOrganizations(id);

    sendSuccess(res, organizations);
  } catch (error) {
    next(error);
  }
}
