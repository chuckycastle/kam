import { Request, Response, NextFunction } from 'express';
import { sendSuccess, sendNotFound } from '../utils/response.js';
import { organizationService } from '../services/index.js';

export async function listOrganizations(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const filters = {
      page: Number(req.query.page) || undefined,
      limit: Number(req.query.limit) || undefined,
      search: req.query.search as string | undefined,
      categoryId: req.query.categoryId as string | undefined,
      assignedToId: req.query.assignedToId as string | undefined,
      isAvailable:
        req.query.isAvailable === 'true'
          ? true
          : req.query.isAvailable === 'false'
            ? false
            : undefined,
    };

    const result = await organizationService.listOrganizations(filters);

    sendSuccess(res, result.organizations, 200, result.meta);
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

    const organization = await organizationService.getOrganizationById(id);

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

    const organization = await organizationService.createOrganization({
      name: data.name,
      description: data.description,
      url: data.url || null,
      contactName: data.contactName,
      contactTitle: data.contactTitle,
      contactPhone: data.contactPhone,
      contactEmail: data.contactEmail || null,
      categoryId: data.categoryId,
      assignedToId: data.assignedToId,
    });

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

    const exists = await organizationService.organizationExists(id);
    if (!exists) {
      sendNotFound(res, 'Organization');
      return;
    }

    const organization = await organizationService.updateOrganization(id, data);

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

    const exists = await organizationService.organizationExists(id);
    if (!exists) {
      sendNotFound(res, 'Organization');
      return;
    }

    await organizationService.deleteOrganization(id);

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

    const organization = await organizationService.assignOrganization(
      id,
      userId
    );

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

    const items = await organizationService.getOrganizationItems(id);

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

    const notes = await organizationService.getOrganizationNotes(id);

    sendSuccess(res, notes);
  } catch (error) {
    next(error);
  }
}
