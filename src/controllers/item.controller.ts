import { Request, Response, NextFunction } from 'express';
import { sendSuccess, sendNotFound, sendForbidden } from '../utils/response.js';
import {
  itemService,
  organizationService,
  isAdmin,
} from '../services/index.js';

export async function listItems(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const filters = {
      page: Number(req.query.page) || undefined,
      limit: Number(req.query.limit) || undefined,
      search: req.query.search as string | undefined,
      organizationId: req.query.organizationId as string | undefined,
      createdById: req.query.createdById as string | undefined,
      isReceived:
        req.query.isReceived === 'true'
          ? true
          : req.query.isReceived === 'false'
            ? false
            : undefined,
    };

    const result = await itemService.listItems(filters);

    sendSuccess(res, result.items, 200, result.meta);
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

    const item = await itemService.getItemById(id);

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
    const orgExists = await organizationService.organizationExists(
      data.organizationId
    );
    if (!orgExists) {
      sendNotFound(res, 'Organization');
      return;
    }

    const item = await itemService.createItem({
      name: data.name,
      description: data.description,
      value: data.value,
      quantity: data.quantity,
      organizationId: data.organizationId,
      createdById: userId,
    });

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

    const ownership = await itemService.getItemOwnership(id);
    if (!ownership.exists) {
      sendNotFound(res, 'Item');
      return;
    }

    // Only creator or admin can update
    if (ownership.createdById !== userId && !isAdmin(userRole)) {
      sendForbidden(res, 'You can only update items you created');
      return;
    }

    const item = await itemService.updateItem(id, data);

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

    const ownership = await itemService.getItemOwnership(id);
    if (!ownership.exists) {
      sendNotFound(res, 'Item');
      return;
    }

    await itemService.deleteItem(id);

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

    const ownership = await itemService.getItemOwnership(id);
    if (!ownership.exists) {
      sendNotFound(res, 'Item');
      return;
    }

    const item = await itemService.markItemReceived(id);

    sendSuccess(res, item);
  } catch (error) {
    next(error);
  }
}
