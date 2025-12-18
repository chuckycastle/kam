import { Request, Response, NextFunction } from 'express';
import { supabase } from '../config/database.js';
import { prisma } from '../config/database.js';
import { sendSuccess, sendError, sendUnauthorized } from '../utils/response.js';
import { logger } from '../utils/logger.js';
import type {
  LoginInput,
  RegisterInput,
  ForgotPasswordInput,
  ResetPasswordInput,
  ChangePasswordInput,
} from '../schemas/auth.schema.js';

export async function register(
  req: Request<unknown, unknown, RegisterInput>,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { email, username, password, firstName, lastName } = req.body;

    // Check if username already exists
    const existingUser = await prisma.user.findUnique({
      where: { username },
    });

    if (existingUser) {
      sendError(res, 'USERNAME_EXISTS', 'Username already taken', 400);
      return;
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
      sendError(res, 'REGISTRATION_FAILED', authError.message, 400);
      return;
    }

    if (!authData.user) {
      sendError(res, 'REGISTRATION_FAILED', 'Failed to create user', 400);
      return;
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

    sendSuccess(
      res,
      {
        message:
          'Registration successful. Please check your email to verify your account.',
        user: {
          id: user.id,
          email: user.email,
          username: user.username,
        },
      },
      201
    );
  } catch (error) {
    next(error);
  }
}

export async function login(
  req: Request<unknown, unknown, LoginInput>,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { email, password } = req.body;

    const { data, error } = await supabase.auth.signInWithPassword({
      email,
      password,
    });

    if (error) {
      sendUnauthorized(res, 'Invalid email or password');
      return;
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
      sendUnauthorized(res, 'Account is inactive');
      return;
    }

    // Set cookie with token
    res.cookie('kam.token', data.session.access_token, {
      httpOnly: true,
      secure: process.env.NODE_ENV === 'production',
      sameSite: 'lax',
      maxAge: 24 * 60 * 60 * 1000, // 24 hours
    });

    logger.info({ userId: user.id }, 'User logged in');

    sendSuccess(res, {
      user,
      token: data.session.access_token,
    });
  } catch (error) {
    next(error);
  }
}

export async function logout(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    // Get token
    const token = req.cookies?.['kam.token'];

    if (token) {
      await supabase.auth.signOut();
    }

    // Clear cookie
    res.clearCookie('kam.token');

    sendSuccess(res, { message: 'Logged out successfully' });
  } catch (error) {
    next(error);
  }
}

export async function forgotPassword(
  req: Request<unknown, unknown, ForgotPasswordInput>,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { email } = req.body;

    const { error } = await supabase.auth.resetPasswordForEmail(email, {
      redirectTo: `${process.env.APP_URL}/auth/reset-password`,
    });

    if (error) {
      logger.error({ error }, 'Password reset request failed');
    }

    // Always return success to prevent email enumeration
    sendSuccess(res, {
      message:
        'If an account with that email exists, a password reset link has been sent.',
    });
  } catch (error) {
    next(error);
  }
}

export async function getCurrentUser(
  req: Request,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const token =
      req.headers.authorization?.substring(7) || req.cookies?.['kam.token'];

    if (!token) {
      sendSuccess(res, { user: null });
      return;
    }

    const {
      data: { user: authUser },
    } = await supabase.auth.getUser(token);

    if (!authUser) {
      sendSuccess(res, { user: null });
      return;
    }

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

    sendSuccess(res, { user });
  } catch (error) {
    next(error);
  }
}

export async function resetPassword(
  req: Request<unknown, unknown, ResetPasswordInput>,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { token, password } = req.body;

    // Use the token to update user's password via Supabase
    const { error } = await supabase.auth.verifyOtp({
      token_hash: token,
      type: 'recovery',
    });

    if (error) {
      logger.error({ error }, 'Password reset verification failed');
      sendError(res, 'RESET_FAILED', 'Invalid or expired reset token', 400);
      return;
    }

    // Update the password
    const { error: updateError } = await supabase.auth.updateUser({
      password,
    });

    if (updateError) {
      logger.error({ error: updateError }, 'Password update failed');
      sendError(res, 'RESET_FAILED', 'Failed to update password', 400);
      return;
    }

    logger.info('Password reset successful');

    sendSuccess(res, {
      message:
        'Password has been reset successfully. You can now log in with your new password.',
    });
  } catch (error) {
    next(error);
  }
}

export async function changePassword(
  req: Request<unknown, unknown, ChangePasswordInput>,
  res: Response,
  next: NextFunction
): Promise<void> {
  try {
    const { currentPassword, newPassword } = req.body;
    const userId = req.user!.id;

    // Get user's email for re-authentication
    const user = await prisma.user.findUnique({
      where: { id: userId },
      select: { email: true },
    });

    if (!user) {
      sendError(res, 'USER_NOT_FOUND', 'User not found', 404);
      return;
    }

    // Verify current password by attempting to sign in
    const { error: authError } = await supabase.auth.signInWithPassword({
      email: user.email,
      password: currentPassword,
    });

    if (authError) {
      sendError(res, 'INVALID_PASSWORD', 'Current password is incorrect', 400);
      return;
    }

    // Update to new password
    const { error: updateError } = await supabase.auth.updateUser({
      password: newPassword,
    });

    if (updateError) {
      logger.error({ error: updateError }, 'Password change failed');
      sendError(res, 'UPDATE_FAILED', 'Failed to update password', 400);
      return;
    }

    logger.info({ userId }, 'Password changed successfully');

    sendSuccess(res, {
      message: 'Password has been changed successfully.',
    });
  } catch (error) {
    next(error);
  }
}
