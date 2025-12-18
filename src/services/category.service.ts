import { prisma } from '../config/database.js';
import { logger } from '../utils/logger.js';

export interface CategoryData {
  id: string;
  name: string;
  title: string;
  description: string | null;
  organizationCount?: number;
}

const categorySelect = {
  id: true,
  title: true,
  description: true,
  _count: { select: { organizations: true } },
};

/**
 * List all categories
 */
export async function listCategories(): Promise<CategoryData[]> {
  const categories = await prisma.category.findMany({
    orderBy: { title: 'asc' },
    select: categorySelect,
  });

  return categories.map((cat) => ({
    id: cat.id,
    name: cat.title,
    title: cat.title,
    description: cat.description,
    organizationCount: cat._count.organizations,
  }));
}

/**
 * Get category by ID
 */
export async function getCategoryById(
  id: string
): Promise<CategoryData | null> {
  const category = await prisma.category.findUnique({
    where: { id },
    select: categorySelect,
  });

  if (!category) return null;

  return {
    id: category.id,
    name: category.title,
    title: category.title,
    description: category.description,
    organizationCount: category._count.organizations,
  };
}

/**
 * Check if category title already exists (case-insensitive)
 */
export async function categoryTitleExists(
  title: string,
  excludeId?: string
): Promise<boolean> {
  const count = await prisma.category.count({
    where: {
      title: { equals: title, mode: 'insensitive' },
      ...(excludeId && { id: { not: excludeId } }),
    },
  });

  return count > 0;
}

/**
 * Create a new category
 */
export async function createCategory(data: {
  title: string;
  description?: string | null;
}): Promise<CategoryData> {
  const category = await prisma.category.create({
    data: {
      title: data.title,
      description: data.description || null,
    },
    select: { id: true, title: true, description: true },
  });

  logger.info({ categoryId: category.id }, 'Category created');

  return {
    id: category.id,
    name: category.title,
    title: category.title,
    description: category.description,
  };
}

/**
 * Update a category
 */
export async function updateCategory(
  id: string,
  data: Partial<{ title: string; description: string | null }>
): Promise<CategoryData> {
  const category = await prisma.category.update({
    where: { id },
    data: {
      ...(data.title && { title: data.title }),
      ...(data.description !== undefined && {
        description: data.description || null,
      }),
    },
    select: { id: true, title: true, description: true },
  });

  logger.info({ categoryId: id }, 'Category updated');

  return {
    id: category.id,
    name: category.title,
    title: category.title,
    description: category.description,
  };
}

/**
 * Check if category can be deleted (has no organizations)
 */
export async function canDeleteCategory(
  id: string
): Promise<{ canDelete: boolean; organizationCount: number }> {
  const category = await prisma.category.findUnique({
    where: { id },
    include: { _count: { select: { organizations: true } } },
  });

  if (!category) {
    return { canDelete: false, organizationCount: 0 };
  }

  return {
    canDelete: category._count.organizations === 0,
    organizationCount: category._count.organizations,
  };
}

/**
 * Delete a category
 */
export async function deleteCategory(id: string): Promise<void> {
  await prisma.category.delete({ where: { id } });
  logger.info({ categoryId: id }, 'Category deleted');
}

/**
 * Check if category exists
 */
export async function categoryExists(id: string): Promise<boolean> {
  const count = await prisma.category.count({ where: { id } });
  return count > 0;
}
