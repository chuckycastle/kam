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
  app.use(helmet({
    contentSecurityPolicy: env.NODE_ENV === 'production',
  }));

  // CORS
  app.use(cors({
    origin: env.NODE_ENV === 'production'
      ? ['https://auction.kriegercenter.org']
      : ['http://localhost:3000', 'http://localhost:5173'],
    credentials: true,
  }));

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

  // 404 handler
  app.use(notFoundHandler);

  // Error handler
  app.use(errorHandler);

  return app;
}
