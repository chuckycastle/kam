import { Request, Response, NextFunction } from 'express';
import { prisma } from '../config/database.js';
import { sendSuccess, sendNotFound } from '../utils/response.js';
import { logger } from '../utils/logger.js';
import { PAGINATION } from '../config/constants.js';

export async function listOrganizations(
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
    const categoryId = req.query.categoryId as string | undefined;
    const assignedToId = req.query.assignedToId as string | undefined;
    const isAvailable =
      req.query.isAvailable === 'true'
        ? true
        : req.query.isAvailable === 'false'
          ? false
          : undefined;
    const sortBy = (req.query.sortBy as string) || 'name';
    const sortOrder = (req.query.sortOrder as 'asc' | 'desc') || 'asc';

    const skip = (page - 1) * limit;

    const where = {
      ...(search && {
        OR: [
          { name: { contains: search, mode: 'insensitive' as const } },
          { contactName: { contains: search, mode: 'insensitive' as const } },
        ],
      }),
      ...(categoryId && { categoryId }),
      ...(assignedToId && { assignedToId }),
      ...(isAvailable !== undefined && { isAvailable }),
    };

    const [organizations, total] = await Promise.all([
      prisma.organization.findMany({
        where,
        skip,
        take: limit,
        orderBy: { [sortBy]: sortOrder },
        include: {
          category: true,
          assignedTo: {
            select: {
              id: true,
              username: true,
              firstName: true,
              lastName: true,
            },
          },
          _count: { select: { items: true, notes: true } },
        },
      }),
      prisma.organization.count({ where }),
    ]);

    sendSuccess(res, organizations, 200, {
      page,
      limit,
      total,
      totalPages: Math.ceil(total / limit),
    });
  } catch (error) {
    next(error);
  }
}

export async function getOrganization(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { id } = req.params;

    const organization = await prisma.organization.findUnique({
      where: { id },
      include: {
        category: true,
        assignedTo: {
          select: {
            id: true,
            username: true,
            firstName: true,
            lastName: true,
            email: true,
          },
        },
        items: {
          orderBy: { createdAt: 'desc' },
          take: 10,
        },
        notes: {
          orderBy: { createdAt: 'desc' },
          take: 5,
          include: {
            author: {
              select: {
                id: true,
                username: true,
                firstName: true,
                lastName: true,
              },
            },
          },
        },
      },
    });

    if (!organization) {
      sendNotFound(res, 'Organization');
      return;
    }

    sendSuccess(res, organization);
  } catch (error) {
    next(error);
  }
}

export async function createOrganization(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const data = req.body;

    const organization = await prisma.organization.create({
      data: {
        name: data.name,
        description: data.description,
        url: data.url || null,
        contactName: data.contactName,
        contactTitle: data.contactTitle,
        contactPhone: data.contactPhone,
        contactEmail: data.contactEmail || null,
        categoryId: data.categoryId,
        assignedToId: data.assignedToId,
      },
      include: {
        category: true,
        assignedTo: {
          select: { id: true, username: true, firstName: true, lastName: true },
        },
      },
    });

    logger.info({ orgId: organization.id }, 'Organization created');

    sendSuccess(res, organization, 201);
  } catch (error) {
    next(error);
  }
}

export async function updateOrganization(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { id } = req.params;
    const data = req.body;

    const existing = await prisma.organization.findUnique({ where: { id } });
    if (!existing) {
      sendNotFound(res, 'Organization');
      return;
    }

    const organization = await prisma.organization.update({
      where: { id },
      data,
      include: {
        category: true,
        assignedTo: {
          select: { id: true, username: true, firstName: true, lastName: true },
        },
      },
    });

    logger.info({ orgId: id }, 'Organization updated');

    sendSuccess(res, organization);
  } catch (error) {
    next(error);
  }
}

export async function deleteOrganization(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { id } = req.params;

    const existing = await prisma.organization.findUnique({ where: { id } });
    if (!existing) {
      sendNotFound(res, 'Organization');
      return;
    }

    await prisma.organization.delete({ where: { id } });

    logger.info({ orgId: id }, 'Organization deleted');

    sendSuccess(res, { message: 'Organization deleted' });
  } catch (error) {
    next(error);
  }
}

export async function assignOrganization(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { id } = req.params;
    const { userId } = req.body;

    const organization = await prisma.organization.update({
      where: { id },
      data: {
        assignedToId: userId,
        isAvailable: false,
      },
      include: {
        assignedTo: {
          select: {
            id: true,
            username: true,
            firstName: true,
            lastName: true,
            email: true,
          },
        },
      },
    });

    logger.info({ orgId: id, userId }, 'Organization assigned');

    sendSuccess(res, organization);
  } catch (error) {
    next(error);
  }
}

export async function getOrganizationItems(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { id } = req.params;

    const items = await prisma.item.findMany({
      where: { organizationId: id },
      orderBy: { createdAt: 'desc' },
      include: {
        createdBy: {
          select: { id: true, username: true, firstName: true, lastName: true },
        },
      },
    });

    sendSuccess(res, items);
  } catch (error) {
    next(error);
  }
}

export async function getOrganizationNotes(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { id } = req.params;

    const notes = await prisma.note.findMany({
      where: { organizationId: id },
      orderBy: { createdAt: 'desc' },
      include: {
        author: {
          select: { id: true, username: true, firstName: true, lastName: true },
        },
      },
    });

    sendSuccess(res, notes);
  } catch (error) {
    next(error);
  }
}
