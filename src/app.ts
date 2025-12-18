import express, { Express } from 'express';
import helmet from 'helmet';
import cors from 'cors';
import cookieParser from 'cookie-parser';
import path from 'path';

import { env } from './config/env.js';
import { errorHandler, notFoundHandler } from './middleware/error-handler.js';
import { optionalAuth } from './middleware/auth.js';
import apiRoutes from './routes/index.js';
import { logger } from './utils/logger.js';

// Using process.cwd() for paths since we run from the app directory

export function createApp(): Express {
  const app = express();

  // Security middleware
  app.use(
    helmet({
      contentSecurityPolicy:
        env.NODE_ENV === 'production'
          ? {
              directives: {
                defaultSrc: ["'self'"],
                scriptSrc: ["'self'", 'https://cdn.jsdelivr.net'],
                styleSrc: [
                  "'self'",
                  "'unsafe-inline'",
                  'https://cdn.jsdelivr.net',
                ],
                fontSrc: ["'self'", 'https://cdn.jsdelivr.net'],
                imgSrc: ["'self'", 'data:', 'https:'],
                connectSrc: ["'self'"],
              },
            }
          : false,
    })
  );

  // CORS
  app.use(
    cors({
      origin:
        env.NODE_ENV === 'production'
          ? ['https://auction.kriegercenter.org']
          : ['http://localhost:3000', 'http://localhost:5173'],
      credentials: true,
    })
  );

  // Body parsing
  app.use(express.json({ limit: '10mb' }));
  app.use(express.urlencoded({ extended: true }));

  // Cookies
  app.use(cookieParser());

  // Request logging
  app.use((req, _res, next) => {
    logger.debug({ method: req.method, path: req.path }, 'Request received');
    next();
  });

  // Static files
  app.use(express.static(path.join(process.cwd(), 'public')));

  // View engine (EJS)
  app.set('view engine', 'ejs');
  app.set('views', path.join(process.cwd(), 'views'));

  // Optional auth for all routes (sets req.user if authenticated)
  app.use(optionalAuth);

  // Health check endpoint
  app.get('/health', (_req, res) => {
    res.json({ status: 'ok', timestamp: new Date().toISOString() });
  });

  // API routes
  app.use('/api', apiRoutes);

  // View routes (placeholder for SSR pages)
  app.get('/', (_req, res) => {
    res.render('pages/home', {
      title: 'Krieger Auction Manager',
      user: null,
    });
  });

  app.get('/login', (_req, res) => {
    res.render('pages/login', {
      title: 'Login - KAM',
      user: null,
    });
  });

  app.get('/register', (_req, res) => {
    res.render('pages/register', {
      title: 'Register - KAM',
      user: null,
    });
  });

  app.get('/forgot-password', (_req, res) => {
    res.render('pages/forgot-password', {
      title: 'Forgot Password - KAM',
      user: null,
    });
  });

  // Reset password page (also handle /auth/reset-password for Supabase redirect)
  app.get(['/reset-password', '/auth/reset-password'], (_req, res) => {
    res.render('pages/reset-password', {
      title: 'Reset Password - KAM',
      user: null,
    });
  });

  app.get('/dashboard', (req, res) => {
    if (!req.user) {
      res.redirect('/login');
      return;
    }
    res.render('pages/dashboard', {
      title: 'Dashboard - KAM',
      user: req.user,
    });
  });

  app.get('/profile', (req, res) => {
    if (!req.user) {
      res.redirect('/login');
      return;
    }
    res.render('pages/profile', {
      title: 'My Profile - KAM',
      user: req.user,
    });
  });

  // Organizations pages
  app.get('/organizations', (req, res) => {
    if (!req.user) {
      res.redirect('/login');
      return;
    }
    res.render('pages/organizations/list', {
      title: 'Organizations - KAM',
      user: req.user,
    });
  });

  app.get('/organizations/new', (req, res) => {
    if (!req.user) {
      res.redirect('/login');
      return;
    }
    res.render('pages/organizations/form', {
      title: 'New Organization - KAM',
      user: req.user,
    });
  });

  app.get('/organizations/:id', (req, res) => {
    if (!req.user) {
      res.redirect('/login');
      return;
    }
    res.render('pages/organizations/detail', {
      title: 'Organization - KAM',
      user: req.user,
    });
  });

  app.get('/organizations/:id/edit', (req, res) => {
    if (!req.user) {
      res.redirect('/login');
      return;
    }
    res.render('pages/organizations/form', {
      title: 'Edit Organization - KAM',
      user: req.user,
    });
  });

  // Items pages
  app.get('/items', (req, res) => {
    if (!req.user) {
      res.redirect('/login');
      return;
    }
    res.render('pages/items/list', {
      title: 'Items - KAM',
      user: req.user,
    });
  });

  app.get('/items/new', (req, res) => {
    if (!req.user) {
      res.redirect('/login');
      return;
    }
    res.render('pages/items/form', {
      title: 'New Item - KAM',
      user: req.user,
    });
  });

  app.get('/items/:id', (req, res) => {
    if (!req.user) {
      res.redirect('/login');
      return;
    }
    res.render('pages/items/detail', {
      title: 'Item - KAM',
      user: req.user,
    });
  });

  app.get('/items/:id/edit', (req, res) => {
    if (!req.user) {
      res.redirect('/login');
      return;
    }
    res.render('pages/items/form', {
      title: 'Edit Item - KAM',
      user: req.user,
    });
  });

  // Reports page
  app.get('/reports', (req, res) => {
    if (!req.user) {
      res.redirect('/login');
      return;
    }
    res.render('pages/reports', {
      title: 'Reports - KAM',
      user: req.user,
    });
  });

  // Admin pages
  app.get('/admin', (req, res) => {
    if (!req.user) {
      res.redirect('/login');
      return;
    }
    // Check if user is admin
    if (req.user.role !== 'ADMIN' && req.user.role !== 'SUPER_ADMIN') {
      res.redirect('/dashboard');
      return;
    }
    res.render('pages/admin/index', {
      title: 'Admin - KAM',
      user: req.user,
    });
  });

  // 404 handler
  app.use(notFoundHandler);

  // Error handler
  app.use(errorHandler);

  return app;
}
