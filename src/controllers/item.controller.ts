import { Request, Response, NextFunction } from 'express';
import { prisma } from '../config/database.js';
import { sendSuccess, sendNotFound, sendForbidden } from '../utils/response.js';
import { logger } from '../utils/logger.js';
import { PAGINATION } from '../config/constants.js';

export async function listItems(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const page = Number(req.query.page) || PAGINATION.DEFAULT_PAGE;
    const limit = Math.min(
      Number(req.query.limit) || PAGINATION.DEFAULT_LIMIT,
      PAGINATION.MAX_LIMIT
    );
    const search = req.query.search as string | undefined;
    const organizationId = req.query.organizationId as string | undefined;
    const createdById = req.query.createdById as string | undefined;
    const isReceived =
      req.query.isReceived === 'true'
        ? true
        : req.query.isReceived === 'false'
          ? false
          : undefined;
    const sortBy = (req.query.sortBy as string) || 'createdAt';
    const sortOrder = (req.query.sortOrder as 'asc' | 'desc') || 'desc';

    const skip = (page - 1) * limit;

    const where = {
      ...(search && {
        OR: [
          { name: { contains: search, mode: 'insensitive' as const } },
          { description: { contains: search, mode: 'insensitive' as const } },
        ],
      }),
      ...(organizationId && { organizationId }),
      ...(createdById && { createdById }),
      ...(isReceived !== undefined && { isReceived }),
    };

    const [items, total] = await Promise.all([
      prisma.item.findMany({
        where,
        skip,
        take: limit,
        orderBy: { [sortBy]: sortOrder },
        include: {
          organization: { select: { id: true, name: true } },
          createdBy: {
            select: {
              id: true,
              username: true,
              firstName: true,
              lastName: true,
            },
          },
        },
      }),
      prisma.item.count({ where }),
    ]);

    sendSuccess(res, items, 200, {
      page,
      limit,
      total,
      totalPages: Math.ceil(total / limit),
    });
  } catch (error) {
    next(error);
  }
}

export async function getItem(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { id } = req.params;

    const item = await prisma.item.findUnique({
      where: { id },
      include: {
        organization: {
          select: {
            id: true,
            name: true,
            contactName: true,
            contactEmail: true,
          },
        },
        createdBy: {
          select: { id: true, username: true, firstName: true, lastName: true },
        },
      },
    });

    if (!item) {
      sendNotFound(res, 'Item');
      return;
    }

    sendSuccess(res, item);
  } catch (error) {
    next(error);
  }
}

export async function createItem(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const data = req.body;
    const userId = req.user!.id;

    // Verify organization exists
    const org = await prisma.organization.findUnique({
      where: { id: data.organizationId },
    });

    if (!org) {
      sendNotFound(res, 'Organization');
      return;
    }

    const item = await prisma.item.create({
      data: {
        name: data.name,
        description: data.description,
        value: data.value,
        quantity: data.quantity || 1,
        organizationId: data.organizationId,
        createdById: userId,
      },
      include: {
        organization: { select: { id: true, name: true } },
        createdBy: {
          select: { id: true, username: true, firstName: true, lastName: true },
        },
      },
    });

    logger.info(
      { itemId: item.id, orgId: data.organizationId },
      'Item created'
    );

    sendSuccess(res, item, 201);
  } catch (error) {
    next(error);
  }
}

export async function updateItem(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { id } = req.params;
    const data = req.body;
    const userId = req.user!.id;
    const userRole = req.user!.role;

    const existing = await prisma.item.findUnique({ where: { id } });
    if (!existing) {
      sendNotFound(res, 'Item');
      return;
    }

    // Only creator or admin can update
    if (
      existing.createdById !== userId &&
      userRole !== 'ADMIN' &&
      userRole !== 'SUPER_ADMIN'
    ) {
      sendForbidden(res, 'You can only update items you created');
      return;
    }

    const item = await prisma.item.update({
      where: { id },
      data,
      include: {
        organization: { select: { id: true, name: true } },
        createdBy: {
          select: { id: true, username: true, firstName: true, lastName: true },
        },
      },
    });

    logger.info({ itemId: id }, 'Item updated');

    sendSuccess(res, item);
  } catch (error) {
    next(error);
  }
}

export async function deleteItem(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { id } = req.params;

    const existing = await prisma.item.findUnique({ where: { id } });
    if (!existing) {
      sendNotFound(res, 'Item');
      return;
    }

    await prisma.item.delete({ where: { id } });

    logger.info({ itemId: id }, 'Item deleted');

    sendSuccess(res, { message: 'Item deleted' });
  } catch (error) {
    next(error);
  }
}

export async function markReceived(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { id } = req.params;

    const existing = await prisma.item.findUnique({ where: { id } });
    if (!existing) {
      sendNotFound(res, 'Item');
      return;
    }

    const item = await prisma.item.update({
      where: { id },
      data: {
        isReceived: true,
        receivedAt: new Date(),
      },
      include: {
        organization: { select: { id: true, name: true } },
        createdBy: {
          select: { id: true, username: true, firstName: true, lastName: true },
        },
      },
    });

    logger.info({ itemId: id }, 'Item marked as received');

    // TODO: Send email notification

    sendSuccess(res, item);
  } catch (error) {
    next(error);
  }
}
