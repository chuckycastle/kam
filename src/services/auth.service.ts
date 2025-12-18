import { supabase, prisma } from '../config/database.js';
import { logger } from '../utils/logger.js';
import type { RegisterInput } from '../schemas/auth.schema.js';

export interface AuthResult<T = unknown> {
  success: boolean;
  data?: T;
  error?: { code: string; message: string };
}

export interface UserData {
  id: string;
  email: string;
  username: string;
  firstName: string;
  lastName: string;
  role: string;
  isActive: boolean;
}

export interface LoginResult {
  user: UserData;
  token: string;
}

/**
 * Register a new user with Supabase and create database record
 */
export async function registerUser(
  input: RegisterInput
): Promise<AuthResult<{ user: Pick<UserData, 'id' | 'email' | 'username'> }>> {
  const { email, username, password, firstName, lastName } = input;

  // Check if username already exists
  const existingUser = await prisma.user.findUnique({
    where: { username },
  });

  if (existingUser) {
    return {
      success: false,
      error: { code: 'USERNAME_EXISTS', message: 'Username already taken' },
    };
  }

  // Register with Supabase Auth
  const { data: authData, error: authError } = await supabase.auth.signUp({
    email,
    password,
    options: {
      data: {
        username,
        firstName,
        lastName,
        role: 'VOLUNTEER',
      },
    },
  });

  if (authError) {
    logger.error({ error: authError }, 'Supabase registration failed');
    return {
      success: false,
      error: { code: 'REGISTRATION_FAILED', message: authError.message },
    };
  }

  if (!authData.user) {
    return {
      success: false,
      error: { code: 'REGISTRATION_FAILED', message: 'Failed to create user' },
    };
  }

  // Create user in database
  const user = await prisma.user.create({
    data: {
      id: authData.user.id,
      email,
      username,
      firstName,
      lastName,
      passwordHash: '', // Handled by Supabase
      role: 'VOLUNTEER',
    },
  });

  logger.info({ userId: user.id }, 'User registered');

  return {
    success: true,
    data: {
      user: {
        id: user.id,
        email: user.email,
        username: user.username,
      },
    },
  };
}

/**
 * Authenticate user with email and password
 */
export async function loginUser(
  email: string,
  password: string
): Promise<AuthResult<LoginResult>> {
  const { data, error } = await supabase.auth.signInWithPassword({
    email,
    password,
  });

  if (error) {
    return {
      success: false,
      error: {
        code: 'INVALID_CREDENTIALS',
        message: 'Invalid email or password',
      },
    };
  }

  // Get user from database
  const user = await prisma.user.findUnique({
    where: { email },
    select: {
      id: true,
      email: true,
      username: true,
      firstName: true,
      lastName: true,
      role: true,
      isActive: true,
    },
  });

  if (!user || !user.isActive) {
    return {
      success: false,
      error: { code: 'ACCOUNT_INACTIVE', message: 'Account is inactive' },
    };
  }

  logger.info({ userId: user.id }, 'User logged in');

  return {
    success: true,
    data: {
      user: user as UserData,
      token: data.session.access_token,
    },
  };
}

/**
 * Log out user from Supabase
 */
export async function logoutUser(): Promise<void> {
  await supabase.auth.signOut();
}

/**
 * Request password reset email
 */
export async function requestPasswordReset(
  email: string,
  redirectUrl: string
): Promise<void> {
  const { error } = await supabase.auth.resetPasswordForEmail(email, {
    redirectTo: redirectUrl,
  });

  if (error) {
    logger.error({ error }, 'Password reset request failed');
  }
  // Always succeed to prevent email enumeration
}

/**
 * Reset password with token
 */
export async function resetPassword(
  token: string,
  newPassword: string
): Promise<AuthResult> {
  const { error: verifyError } = await supabase.auth.verifyOtp({
    token_hash: token,
    type: 'recovery',
  });

  if (verifyError) {
    logger.error({ error: verifyError }, 'Password reset verification failed');
    return {
      success: false,
      error: {
        code: 'RESET_FAILED',
        message: 'Invalid or expired reset token',
      },
    };
  }

  const { error: updateError } = await supabase.auth.updateUser({
    password: newPassword,
  });

  if (updateError) {
    logger.error({ error: updateError }, 'Password update failed');
    return {
      success: false,
      error: { code: 'RESET_FAILED', message: 'Failed to update password' },
    };
  }

  logger.info('Password reset successful');
  return { success: true };
}

/**
 * Change password for authenticated user
 */
export async function changePassword(
  userId: string,
  currentPassword: string,
  newPassword: string
): Promise<AuthResult> {
  // Get user's email for re-authentication
  const user = await prisma.user.findUnique({
    where: { id: userId },
    select: { email: true },
  });

  if (!user) {
    return {
      success: false,
      error: { code: 'USER_NOT_FOUND', message: 'User not found' },
    };
  }

  // Verify current password
  const { error: authError } = await supabase.auth.signInWithPassword({
    email: user.email,
    password: currentPassword,
  });

  if (authError) {
    return {
      success: false,
      error: {
        code: 'INVALID_PASSWORD',
        message: 'Current password is incorrect',
      },
    };
  }

  // Update to new password
  const { error: updateError } = await supabase.auth.updateUser({
    password: newPassword,
  });

  if (updateError) {
    logger.error({ error: updateError }, 'Password change failed');
    return {
      success: false,
      error: { code: 'UPDATE_FAILED', message: 'Failed to update password' },
    };
  }

  logger.info({ userId }, 'Password changed successfully');
  return { success: true };
}

/**
 * Get current user from token
 */
export async function getCurrentUser(
  token: string | undefined
): Promise<UserData | null> {
  if (!token) return null;

  const {
    data: { user: authUser },
  } = await supabase.auth.getUser(token);

  if (!authUser) return null;

  const user = await prisma.user.findUnique({
    where: { id: authUser.id },
    select: {
      id: true,
      email: true,
      username: true,
      firstName: true,
      lastName: true,
      role: true,
      isActive: true,
    },
  });

  return user as UserData | null;
}
