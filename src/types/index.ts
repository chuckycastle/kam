import { Role } from '../config/constants.js';

// User types
export interface User {
  id: string;
  email: string;
  username: string;
  firstName: string;
  lastName: string;
  role: Role;
  isActive: boolean;
  createdAt: Date;
  updatedAt: Date;
  supervisorId?: string | null;
}

export interface CreateUserInput {
  email: string;
  username: string;
  password: string;
  firstName: string;
  lastName: string;
  role?: Role;
  supervisorId?: string;
}

export interface UpdateUserInput {
  email?: string;
  username?: string;
  firstName?: string;
  lastName?: string;
  role?: Role;
  isActive?: boolean;
  supervisorId?: string | null;
}

// Organization types
export interface Organization {
  id: string;
  name: string;
  description?: string | null;
  url?: string | null;
  contactName?: string | null;
  contactTitle?: string | null;
  contactPhone?: string | null;
  contactEmail?: string | null;
  isAvailable: boolean;
  categoryId?: string | null;
  assignedToId?: string | null;
  createdAt: Date;
  updatedAt: Date;
}

export interface CreateOrganizationInput {
  name: string;
  description?: string;
  url?: string;
  contactName?: string;
  contactTitle?: string;
  contactPhone?: string;
  contactEmail?: string;
  categoryId?: string;
  assignedToId?: string;
}

export interface UpdateOrganizationInput {
  name?: string;
  description?: string;
  url?: string;
  contactName?: string;
  contactTitle?: string;
  contactPhone?: string;
  contactEmail?: string;
  isAvailable?: boolean;
  categoryId?: string | null;
  assignedToId?: string | null;
}

// Item types
export interface Item {
  id: string;
  name: string;
  description?: string | null;
  value?: number | null;
  quantity: number;
  isReceived: boolean;
  receivedAt?: Date | null;
  messageSent: boolean;
  organizationId: string;
  createdById: string;
  createdAt: Date;
  updatedAt: Date;
}

export interface CreateItemInput {
  name: string;
  description?: string;
  value?: number;
  quantity?: number;
  organizationId: string;
}

export interface UpdateItemInput {
  name?: string;
  description?: string;
  value?: number;
  quantity?: number;
  isReceived?: boolean;
}

// Note types
export interface Note {
  id: string;
  content: string;
  organizationId: string;
  authorId: string;
  createdAt: Date;
  updatedAt: Date;
}

export interface CreateNoteInput {
  content: string;
  organizationId: string;
}

// Category types
export interface Category {
  id: string;
  title: string;
  description?: string | null;
}

// Pagination types
export interface PaginationParams {
  page?: number;
  limit?: number;
}

export interface PaginatedResult<T> {
  data: T[];
  meta: {
    page: number;
    limit: number;
    total: number;
    totalPages: number;
  };
}

// Search types
export interface SearchParams {
  q?: string;
  page?: number;
  limit?: number;
}
