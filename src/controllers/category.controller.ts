import { Request, Response, NextFunction } from 'express';
import { prisma } from '../config/database.js';
import { sendSuccess, sendNotFound, sendError } from '../utils/response.js';
import { logger } from '../utils/logger.js';
import type {
  CreateCategoryInput,
  UpdateCategoryInput,
} from '../schemas/category.schema.js';

export async function listCategories(
  _req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const categories = await prisma.category.findMany({
      orderBy: { title: 'asc' },
      select: {
        id: true,
        title: true,
        description: true,
        _count: { select: { organizations: true } },
      },
    });

    // Map for frontend consistency
    const mappedCategories = categories.map((cat) => ({
      id: cat.id,
      name: cat.title,
      title: cat.title,
      description: cat.description,
      organizationCount: cat._count.organizations,
    }));

    sendSuccess(res, mappedCategories);
  } catch (error) {
    next(error);
  }
}

export async function getCategory(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { id } = req.params;

    const category = await prisma.category.findUnique({
      where: { id },
      select: {
        id: true,
        title: true,
        description: true,
        _count: { select: { organizations: true } },
      },
    });

    if (!category) {
      sendNotFound(res, 'Category');
      return;
    }

    sendSuccess(res, {
      id: category.id,
      name: category.title,
      title: category.title,
      description: category.description,
      organizationCount: category._count.organizations,
    });
  } catch (error) {
    next(error);
  }
}

export async function createCategory(
  req: Request<unknown, unknown, CreateCategoryInput>,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { title, description } = req.body;

    // Check for duplicate title
    const existing = await prisma.category.findFirst({
      where: { title: { equals: title, mode: 'insensitive' } },
    });

    if (existing) {
      sendError(
        res,
        'DUPLICATE_CATEGORY',
        'A category with this name already exists',
        400
      );
      return;
    }

    const category = await prisma.category.create({
      data: {
        title,
        description: description || null,
      },
      select: {
        id: true,
        title: true,
        description: true,
      },
    });

    logger.info({ categoryId: category.id }, 'Category created');

    sendSuccess(
      res,
      {
        id: category.id,
        name: category.title,
        title: category.title,
        description: category.description,
      },
      201
    );
  } catch (error) {
    next(error);
  }
}

export async function updateCategory(
  req: Request<{ id: string }, unknown, UpdateCategoryInput>,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { id } = req.params;
    const { title, description } = req.body;

    const existing = await prisma.category.findUnique({ where: { id } });
    if (!existing) {
      sendNotFound(res, 'Category');
      return;
    }

    // Check for duplicate title if changing
    if (title && title !== existing.title) {
      const duplicate = await prisma.category.findFirst({
        where: {
          title: { equals: title, mode: 'insensitive' },
          id: { not: id },
        },
      });

      if (duplicate) {
        sendError(
          res,
          'DUPLICATE_CATEGORY',
          'A category with this name already exists',
          400
        );
        return;
      }
    }

    const category = await prisma.category.update({
      where: { id },
      data: {
        ...(title && { title }),
        ...(description !== undefined && { description: description || null }),
      },
      select: {
        id: true,
        title: true,
        description: true,
      },
    });

    logger.info({ categoryId: id }, 'Category updated');

    sendSuccess(res, {
      id: category.id,
      name: category.title,
      title: category.title,
      description: category.description,
    });
  } catch (error) {
    next(error);
  }
}

export async function deleteCategory(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { id } = req.params;

    const existing = await prisma.category.findUnique({
      where: { id },
      include: { _count: { select: { organizations: true } } },
    });

    if (!existing) {
      sendNotFound(res, 'Category');
      return;
    }

    // Prevent deletion if category has organizations
    if (existing._count.organizations > 0) {
      sendError(
        res,
        'CATEGORY_IN_USE',
        `Cannot delete category with ${existing._count.organizations} organization(s). Reassign them first.`,
        400
      );
      return;
    }

    await prisma.category.delete({ where: { id } });

    logger.info({ categoryId: id }, 'Category deleted');

    sendSuccess(res, { message: 'Category deleted' });
  } catch (error) {
    next(error);
  }
}
