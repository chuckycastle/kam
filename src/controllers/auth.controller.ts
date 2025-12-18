import { Request, Response, NextFunction } from 'express';
import { sendSuccess, sendError, sendUnauthorized } from '../utils/response.js';
import { authService } from '../services/index.js';
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
    const result = await authService.registerUser(req.body);

    if (!result.success) {
      sendError(res, result.error!.code, result.error!.message, 400);
      return;
    }

    sendSuccess(
      res,
      {
        message:
          'Registration successful. Please check your email to verify your account.',
        user: result.data!.user,
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

    const result = await authService.loginUser(email, password);

    if (!result.success) {
      sendUnauthorized(
        res,
        result.error?.message || 'Invalid email or password'
      );
      return;
    }

    // Set cookie with token
    res.cookie('kam.token', result.data!.token, {
      httpOnly: true,
      secure: process.env.NODE_ENV === 'production',
      sameSite: 'lax',
      maxAge: 24 * 60 * 60 * 1000, // 24 hours
    });

    sendSuccess(res, {
      user: result.data!.user,
      token: result.data!.token,
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
    const token = req.cookies?.['kam.token'];

    if (token) {
      await authService.logoutUser();
    }

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
    const redirectUrl = `${process.env.APP_URL}/auth/reset-password`;

    await authService.requestPasswordReset(email, redirectUrl);

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

    const user = await authService.getCurrentUser(token);

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

    const result = await authService.resetPassword(token, password);

    if (!result.success) {
      sendError(
        res,
        result.error?.code || 'RESET_FAILED',
        result.error?.message || 'Password reset failed',
        400
      );
      return;
    }

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

    const result = await authService.changePassword(
      userId,
      currentPassword,
      newPassword
    );

    if (!result.success) {
      const statusCode = result.error?.code === 'USER_NOT_FOUND' ? 404 : 400;
      sendError(
        res,
        result.error?.code || 'UPDATE_FAILED',
        result.error?.message || 'Password change failed',
        statusCode
      );
      return;
    }

    sendSuccess(res, {
      message: 'Password has been changed successfully.',
    });
  } catch (error) {
    next(error);
  }
}
