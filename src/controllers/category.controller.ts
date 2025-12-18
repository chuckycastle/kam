import { Request, Response, NextFunction } from 'express';
import { sendSuccess, sendNotFound, sendError } from '../utils/response.js';
import { categoryService } from '../services/index.js';
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
    const categories = await categoryService.listCategories();

    sendSuccess(res, categories);
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

    const category = await categoryService.getCategoryById(id);

    if (!category) {
      sendNotFound(res, 'Category');
      return;
    }

    sendSuccess(res, category);
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
    const exists = await categoryService.categoryTitleExists(title);
    if (exists) {
      sendError(
        res,
        'DUPLICATE_CATEGORY',
        'A category with this name already exists',
        400
      );
      return;
    }

    const category = await categoryService.createCategory({
      title,
      description,
    });

    sendSuccess(res, category, 201);
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

    const existing = await categoryService.getCategoryById(id);
    if (!existing) {
      sendNotFound(res, 'Category');
      return;
    }

    // Check for duplicate title if changing
    if (title && title !== existing.title) {
      const duplicate = await categoryService.categoryTitleExists(title, id);
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

    const category = await categoryService.updateCategory(id, {
      title,
      description,
    });

    sendSuccess(res, category);
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

    const canDelete = await categoryService.canDeleteCategory(id);

    if (!canDelete.canDelete && canDelete.organizationCount === 0) {
      sendNotFound(res, 'Category');
      return;
    }

    if (!canDelete.canDelete) {
      sendError(
        res,
        'CATEGORY_IN_USE',
        `Cannot delete category with ${canDelete.organizationCount} organization(s). Reassign them first.`,
        400
      );
      return;
    }

    await categoryService.deleteCategory(id);

    sendSuccess(res, { message: 'Category deleted' });
  } catch (error) {
    next(error);
  }
}
